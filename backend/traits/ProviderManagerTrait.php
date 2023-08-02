<?php

namespace backend\traits;

use backend\helpers\RedisKeys;
use common\models\Provider;

trait ProviderManagerTrait
{
    public function saveProvider()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        $provider = $this->getProviderModel()->one();

        if (empty($provider)) {
            $provider = new Provider([
                'name' => $this->provider,
                'business_id' => $business['id']
            ]);
            $provider->save();
        }
    }

    public function getProviderModel()
    {
        return Provider::find()->where(['name' => $this->provider, 'business_id' => $this->business_id]);
    }
}
