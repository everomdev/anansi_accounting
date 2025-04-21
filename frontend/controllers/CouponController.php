<?php

namespace frontend\controllers;

use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\Coupon;
use common\models\CouponSearch;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CouponController implements the CRUD actions for Coupon model.
 */
class CouponController extends Controller
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Coupon models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CouponSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Coupon model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Coupon model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Coupon();
        $post = Yii::$app->request->post();
        
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post)) {
            // Ensure the date format is correct
            if (isset($model->date)) {
                $model->date = date('Y-m-d H:i:s', strtotime($model->date));
            }
            if (isset($model->expiration_date)) {
                $model->expiration_date = strtotime($model->expiration_date);
            }
            // Crear el cupón en Stripe antes de guardar en la base de datos local
            $stripeId = $this->createStripePromoCode($model);
            
            if ($stripeId) {
                // Guardar el ID de Stripe en el modelo
                $model->stripe_coupon_id = $stripeId;
                
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Cupón creado correctamente y sincronizado con Stripe.');
                    return $this->redirect(['index']);
                }
            } else {
                // Si falló la creación en Stripe, mostrar error
                Yii::$app->session->setFlash('error', 'Error al crear el cupón en Stripe. El cupón no se ha guardado.');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    /**
     * Updates an existing Coupon model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
    
    // Convertir fechas al formato requerido por el input date HTML5
    if ($model->expiration) {
        if (is_numeric($model->expiration)) {
            // Si es un timestamp
            $formatted_expiration = date('Y-m-d', $model->expiration);
        } else {
            // Si es una cadena en formato datetime
            $expiration_time = strtotime($model->expiration);
            $formatted_expiration = date('Y-m-d', $expiration_time);
        }
        $model->expiration_formatted = $formatted_expiration;
    }
    
    if ($model->expiration_date) {
        if (is_numeric($model->expiration_date)) {
            // Si es un timestamp
            $formatted_expiration_date = date('Y-m-d', $model->expiration_date);
        } else {
            // Si es una cadena en formato datetime
            $expiration_date_time = strtotime($model->expiration_date);
            $formatted_expiration_date = date('Y-m-d', $expiration_date_time);
        }
        $model->expiration_date_formatted = $formatted_expiration_date;
    }

    $post = Yii::$app->request->post();
    if (array_key_exists('ajax', $post)) {
        $this->make(AjaxRequestModelValidator::class, [$model])->validate();
    }
    
    // Eliminar la línea de depuración
    // die(var_dump($model->expiration));

    if ($model->load($post)) {
        // Procesar las fechas antes de guardar
        if (isset($model->expiration)) {
            // Asegurarse de guardar en el formato correcto (timestamp o datetime string)
            // Dependiendo de cómo está configurada tu base de datos
            $model->expiration = date('Y-m-d H:i:s', strtotime($model->expiration));
        }
        
        if (isset($model->expiration_date)) {
            // Convertir a timestamp como en actionCreate
            $model->expiration_date = strtotime($model->expiration_date);
        }
        
        if ($model->save()) {
            return $this->redirect(['index']);
        }
    }

    return $this->render('update', [
        'model' => $model,
    ]);
}

    /**
     * Deletes an existing Coupon model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Si existe un ID de cupón en Stripe, intentar eliminarlo primero
        if (!empty($model->stripe_coupon_id)) {
            $this->deleteStripePromoCode($model->stripe_coupon_id);
        }
        
        $model->delete();

        Yii::$app->session->setFlash('success', 'Cupón eliminado correctamente.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Coupon model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Coupon the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Coupon::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    /**
     * Crea un código promocional en Stripe
     * @param Coupon $model El modelo de cupón
     * @return string|null El ID del cupón creado en Stripe o null si hubo un error
     */
    protected function createStripePromoCode($model)
    {
        try {
            // Configurar la API de Stripe
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);
            
            $duration = 'once'; // Por defecto, el cupón se aplica una sola vez
            
            // Calcular duración y redondear al valor entero en días
            $durationInDays = null;
            if (isset($model->expiration_date) && $model->expiration_date > time()) {
                $durationInDays = ceil(($model->expiration_date - time()) / 86400); // 86400 segundos en un día
                
                // Si es mayor a 365 días, limitar a un año
                if ($durationInDays > 365) {
                    $durationInDays = 365;
                }
                
                // Si es muy corto, establecer mínimo 1 día
                if ($durationInDays < 1) {
                    $durationInDays = 1;
                }
            }
            
            $couponData = [
                'id' => $model->code,
                'name' => $model->name ?: $model->code,
                'duration' => $duration,
                'redeem_by' => $model->expiration_date,
                'max_redemptions' => $model->quantity ?: null,
                'metadata' => [
                    'created_by' => Yii::$app->user->id,
                    'description' => 'Cupon creado desde el sistema',
                    'plan_id' => $model->plan_id ?: ''
                ]
            ];
            
            if ($model->type == 'per_cent') {
                $couponData['percent_off'] = (float)$model->discount;
            } elseif ($model->type == 'amount') {
                $couponData['amount_off'] = (int)($model->discount * 100); // en centavos
                $couponData['currency'] = 'usd';
            }
            $stripeCoupon = $stripe->coupons->create($couponData);

            
            // Devolver el ID del cupón creado
            return $stripeCoupon->id;
            
        } catch (ApiErrorException $e) {
            // Registrar el error
            Yii::error('Error al crear cupón en Stripe: ' . $e->getMessage(), 'stripe');
            
            // Mostrar mensaje de error
            Yii::$app->session->setFlash('error', 'Error de Stripe: ' . $e->getMessage());
            
            return null;
        }
    }
    protected function deleteStripePromoCode($stripeId)
    {
        try {
            // Configurar la API de Stripe
            $stripe = new \Stripe\StripeClient(Yii::$app->params['stripe.secretKey']);

            
            // Eliminar el cupón
            $deleted = $stripe->coupons->delete($stripeId);
            
            return $deleted->deleted;
            
        } catch (ApiErrorException $e) {
            // Registrar el error
            Yii::error('Error al eliminar cupón en Stripe: ' . $e->getMessage(), 'stripe');
            
            // No mostrar error al usuario, solo registrarlo
            return false;
        }
    }
}
