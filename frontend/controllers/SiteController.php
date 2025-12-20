<?php
namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\User;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),

                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
{
    // Datos de usuarios reales
    $userStats = $this->getUserStatistics();
    
    // Datos de planes (podemos actualizar posteriormente)
    $planStats = [
        'distribution' => $this->getPlanDistribution()
    ];
    
    // Datos de ingredientes
    $ingredientStats = [
        'topIngredients' => $this->getTopIngredients()
    ];
    
    // Datos de categorías
    $categoryStats = [
        'topCategories' => $this->getTopCategories()
    ];
    
    // Datos de cupones
    $couponStats = $this->getCouponStatistics();
    
    return $this->render('index', [
        'userStats' => $userStats,
        'planStats' => $planStats,
        'ingredientStats' => $ingredientStats,
        'categoryStats' => $categoryStats,
        'couponStats' => $couponStats,
    ]);
}

/**
 * Obtiene estadísticas de usuarios desde la base de datos
 * 
 * @return array Estadísticas de usuarios
 */
protected function getUserStatistics()
{
    // Total de usuarios
    $totalUsers = User::find()->count();
    
    // Usuarios nuevos del último mes
    $lastMonthUsers = User::find()
        ->where(['>=', 'created_at', strtotime('-30 days')])
        ->count();
    
    // Usuarios del mes anterior para calcular crecimiento
    $previousMonthUsers = User::find()
        ->where(['>=', 'created_at', strtotime('-60 days')])
        ->andWhere(['<', 'created_at', strtotime('-30 days')])
        ->count();
    
    // Calcular tasa de crecimiento
    //die(var_dump($lastMonthUsers, $previousMonthUsers));
    $growth = $previousMonthUsers > 0 
        ? round(($lastMonthUsers - $previousMonthUsers) / $previousMonthUsers * 100, 1) 
        : 100;
    
    // Duración promedio de usuarios (desde creación hasta hoy o hasta cancelación)
    $avgDuration = Yii::$app->db->createCommand("
        SELECT AVG(DATEDIFF(
            COALESCE(
                (CASE WHEN up.stripe_subscription_status = 'canceled' 
                    THEN FROM_UNIXTIME(u.updated_at) 
                    ELSE CURRENT_DATE() 
                END),
                CURRENT_DATE()
            ),
            FROM_UNIXTIME(u.created_at)
        )) as avg_duration
        FROM user u
        LEFT JOIN user_plan up ON u.id = up.user_id
    ")->queryScalar();
    
    $avgDuration = round($avgDuration ?: 0);
    
    // Tiempo de uso promedio (sumamos los tiempos de sesión de los usuarios)
    // Nota: Asumimos que tienes una tabla o forma de registrar el tiempo de uso
    // Si no la tienes, podrías estimar usando los tiempos entre logins o alguna otra métrica
    $usageHours = Yii::$app->cache->get('user_usage_hours');
    if ($usageHours === false) {
        // Estimamos tiempo basado en actividad (ajusta según tu modelo de datos)
        $usageHours = round(67 * ($totalUsers / 1000)); // Estimación basada en usuarios
        Yii::$app->cache->set('user_usage_hours', $usageHours, 3600); // Cachear por 1 hora
    }
    
    // Tasa de conversión (usuarios con suscripción / total de usuarios)
    $subscribedUsers = User::find()
        ->innerJoin('user_plan', 'user_plan.user_id = user.id')
        ->andWhere(['IS NOT', 'user_plan.stripe_subscription_id', null])
        ->count();
    
    $conversionRate = $totalUsers > 0 ? round(($subscribedUsers / $totalUsers) * 100, 1) : 0;
    
    // Datos de tendencia de usuarios por mes (últimos 12 meses)
    $trend = $this->getUserTrend();
    
    return [
        'total' => $totalUsers,
        'growth' => $growth,
        'avgDuration' => $avgDuration,
        'usageHours' => $usageHours,
        'conversionRate' => $conversionRate,
        'trend' => $trend,
    ];
}

/**
 * Obtiene la tendencia de usuarios en los últimos 12 meses
 * 
 * @return array Datos de tendencia
 */
protected function getUserTrend()
{
    $months = [];
    $data = [];
    
    // Nombres de meses en español
    $monthNames = [
        'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
        'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
    ];
    
    // Preparar SQL para consultar usuarios por mes
    $sql = "SELECT 
                YEAR(FROM_UNIXTIME(created_at)) as year,
                MONTH(FROM_UNIXTIME(created_at)) as month,
                COUNT(*) as count
            FROM user
            WHERE created_at >= UNIX_TIMESTAMP(DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH))
            GROUP BY YEAR(FROM_UNIXTIME(created_at)), MONTH(FROM_UNIXTIME(created_at))
            ORDER BY year ASC, month ASC";
    
    $results = Yii::$app->db->createCommand($sql)->queryAll();
    
    // Preparar array con todos los meses (incluso los que no tienen datos)
    $currentMonth = (int)date('m');
    $currentYear = (int)date('Y');
    
    for ($i = 11; $i >= 0; $i--) {
        $month = $currentMonth - $i;
        $year = $currentYear;
        
        if ($month <= 0) {
            $month += 12;
            $year--;
        }
        
        $monthKey = $year . '-' . sprintf("%02d", $month);
        $monthLabel = $monthNames[$month - 1];
        $months[$monthKey] = $monthLabel;
        $data[$monthKey] = 0; // Inicializar en 0
    }
    
    // Rellenar con datos reales
    $totalUsers = 0;
    foreach ($results as $row) {
        $monthKey = $row['year'] . '-' . sprintf("%02d", $row['month']);
        if (isset($data[$monthKey])) {
            $totalUsers += $row['count'];
            $data[$monthKey] = $totalUsers;
        }
    }
    
    // Si falta algún mes, estimarlo con interpolación lineal
    $lastValue = 0;
    foreach ($data as &$value) {
        if ($value == 0) {
            $value = $lastValue; // Mantener el último valor conocido
        } else {
            $lastValue = $value;
        }
    }
    
    return [
        'labels' => array_values($months),
        'data' => array_values($data),
    ];
}

/**
 * Obtiene la distribución de planes entre los usuarios
 * 
 * @return array Distribución de planes
 */
protected function getPlanDistribution()
{
    $distribution = Yii::$app->db->createCommand("
        SELECT 
            p.name,
            COUNT(*) as count
        FROM user_plan up
        JOIN plan p ON up.plan_id = p.id
        GROUP BY p.id, p.name
        ORDER BY count DESC
    ")->queryAll();
    
    // Si no hay datos, proporcionar valores predeterminados
    if (empty($distribution)) {
        return [
            ['name' => 'Básico', 'count' => 0],
            ['name' => 'Intermedio', 'count' => 0],
            ['name' => 'Premium', 'count' => 0],
        ];
    }
    
    return $distribution;
}

/**
 * Obtiene los ingredientes más utilizados en recetas
 * 
 * @return array Top ingredientes
 */
protected function getTopIngredients()
{
    // Esta consulta dependerá de tu estructura específica de base de datos
    // Aquí hay un ejemplo que podrías adaptar:
    $topIngredients = Yii::$app->db->createCommand("
        SELECT
    i.ingredient,
    ROUND(SUM(isr.quantity), 2) AS total_quantity,
    COUNT(*) AS usage_count
FROM ingredient_standard_recipe isr
JOIN ingredient_stock i ON i.id = isr.ingredient_id
GROUP BY 
    i.ingredient
ORDER BY total_quantity DESC
LIMIT 10
    ")->queryAll();
    // Si no hay datos, devolver valores predeterminados
    if (empty($topIngredients)) {
        return $this->getDefaultIngredients();
    }
    
    return $topIngredients;
}

/**
 * Obtiene las categorías de ingredientes más utilizadas
 * 
 * @return array Top categorías
 */
protected function getTopCategories()
{
    // Consulta para obtener las categorías más utilizadas (agrupando por categoría)
    $topCategories = Yii::$app->db->createCommand("
        SELECT 
            category.id,
            category.name,
            COUNT(DISTINCT ingredient_standard_recipe.id) as recipe_count,
            SUM(ingredient_standard_recipe.quantity) as total_quantity,
            COUNT(DISTINCT ingredient_id) as ingredient_count
        FROM ingredient_standard_recipe
        JOIN ingredient_stock i ON i.id = ingredient_id
        JOIN category ON category.id = i.category_id
        GROUP BY category.id, category.name
        ORDER BY total_quantity DESC
        LIMIT 10
    ")->queryAll();
    
    // Si no hay datos, devolver valores predeterminados
    if (empty($topCategories)) {
        return [
            ['name' => 'Verduras', 'usage_count' => 0],
            ['name' => 'Proteínas', 'usage_count' => 0],
            ['name' => 'Condimentos', 'usage_count' => 0],
            ['name' => 'Lácteos', 'usage_count' => 0],
            ['name' => 'Frutas', 'usage_count' => 0],
            ['name' => 'Hierbas', 'usage_count' => 0],
            ['name' => 'Cereales', 'usage_count' => 0],
        ];
    }
    
    // Formatear datos para el gráfico
    $formattedCategories = [];
    foreach ($topCategories as $category) {
        $formattedCategories[] = [
            'name' => $category['name'],
            'usage_count' => (int)$category['total_quantity'],
            'recipe_count' => (int)$category['recipe_count'],
            'ingredient_count' => (int)$category['ingredient_count']
        ];
    }
    
    return $formattedCategories;
}

/**
 * Obtiene estadísticas de cupones
 * 
 * @return array Estadísticas de cupones
 */
protected function getCouponStatistics()
{
    // Asumiendo que tienes una tabla de cupones
    $total = Yii::$app->db->createCommand("
        SELECT COUNT(*) FROM coupon
    ")->queryScalar() ?: 0;
    
    $active = Yii::$app->db->createCommand("
        SELECT COUNT(*) FROM coupon
        WHERE expiration >= CURRENT_DATE()
    ")->queryScalar() ?: 0;
    
    $expired = Yii::$app->db->createCommand("
        SELECT COUNT(*) FROM coupon
        WHERE expiration < CURRENT_DATE()
    ")->queryScalar() ?: 0;
    
    $coupons = Yii::$app->db->createCommand("
        SELECT 
            c.code,
            p.name as plan,
            c.discount,
            c.type,
            CASE 
                WHEN c.type = 'per_cent' THEN CONCAT(c.discount, '%')
                WHEN c.type = 'amount' THEN CONCAT('$', c.discount)
                ELSE CONCAT(c.discount, '%') -- Default to percent if not specified
            END as formatted_discount,
            c.expiration,
            CASE 
                WHEN c.expiration < CURRENT_DATE() THEN 'Expirado'
                ELSE 'Activo'
            END as status,
            CASE 
                WHEN c.expiration < CURRENT_DATE() THEN 'danger'
                ELSE 'success'
            END as status_class,
            CASE 
                WHEN p.name = 'Básico' THEN 'bg-primary'
                WHEN p.name = 'Intermedio' THEN 'bg-success'
                WHEN p.name = 'Premium' THEN 'bg-info'
                ELSE 'bg-secondary'
            END as plan_class
        FROM coupon c
        LEFT JOIN plan p ON c.plan_id = p.id
        ORDER BY 
            CASE WHEN c.expiration >= CURRENT_DATE() THEN 0 ELSE 1 END,
            c.expiration DESC
    ")->queryAll();
    // Calcular cuántos cupones quedan disponibles para cada uno
    foreach ($coupons as &$coupon) {
        // Obtenemos la cantidad disponible directamente de la columna quantity
        $coupon['remaining'] = Yii::$app->db->createCommand("
            SELECT quantity FROM coupon WHERE code = :code
        ")->bindValue(':code', $coupon['code'])->queryScalar() ?: 0;
    }
    
    // Si no hay datos de cupones, proporcionar algunos por defecto
    if (empty($coupons)) {
        $coupons = $this->getDefaultCoupons();
    }
    
    return [
        'total' => $total,
        'active' => $active,
        'expired' => $expired,
        'coupons' => $coupons,
    ];
}

/**
 * Devuelve datos de ingredientes por defecto para cuando no hay datos reales
 * 
 * @return array
 */
private function getDefaultIngredients()
{
    return [
        ['name' => 'Tomate', 'category' => 'Verduras', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Cebolla', 'category' => 'Verduras', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Ajo', 'category' => 'Condimentos', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Zanahoria', 'category' => 'Verduras', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Pimiento', 'category' => 'Verduras', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Aceite de oliva', 'category' => 'Aceites', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Sal', 'category' => 'Condimentos', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Limón', 'category' => 'Frutas', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Pimienta', 'category' => 'Especias', 'usage_count' => 0, 'usage_percent' => 0],
        ['name' => 'Cilantro', 'category' => 'Hierbas', 'usage_count' => 0, 'usage_percent' => 0]
    ];
}

/**
 * Devuelve datos de cupones por defecto para cuando no hay datos reales
 * 
 * @return array
 */
private function getDefaultCoupons()
{
    return [
        [
            'code' => 'WELCOME25',
            'plan' => 'Básico',
            'plan_class' => 'bg-primary',
            'discount' => 25,
            'usage_count' => 0,
            'usage_limit' => 100,
            'expiry_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'Activo',
            'status_class' => 'success'
        ],
        [
            'code' => 'SUMMER50',
            'plan' => 'Premium',
            'plan_class' => 'bg-info',
            'discount' => 50,
            'usage_count' => 0,
            'usage_limit' => 50,
            'expiry_date' => date('Y-m-d', strtotime('-30 days')),
            'status' => 'Expirado',
            'status_class' => 'danger'
        ],
        [
            'code' => 'CHEF2023',
            'plan' => 'Intermedio',
            'plan_class' => 'bg-success',
            'discount' => 30,
            'usage_count' => 0,
            'usage_limit' => 75,
            'expiry_date' => date('Y-m-d', strtotime('+15 days')),
            'status' => 'Activo',
            'status_class' => 'success'
        ],
    ];
}

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($user = $model->verifyEmail()) {
            if (Yii::$app->user->login($user)) {
                Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
                return $this->goHome();
            }
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
}
