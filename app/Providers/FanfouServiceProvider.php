<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FanfouServiceProvider extends \Eva\EvaOAuth\OAuth2\Providers\AbstractProvider
{
    protected $authorizeUrl = 'http://fanfou.com/oauth/authorize';
    protected $accessTokenUrl = 'http://fanfou.com/oauth/access_token';
}
