<?php

namespace backend\tests\functional;

use backend\fixtures\Administrator;
use backend\tests\FunctionalTester;

/**
 * Class LoginCest
 */
class LoginCest
{
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'user' => [
                'class' => Administrator::className(),
                'dataFile' => codecept_data_dir() . 'login_data.php'
            ]
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function loginAdministrator(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');
        $I->fillField('账号1', 'erau');
        $I->fillField('密码1', 'password_0');
        $I->click('login-button');

        $I->see('Logout (erau)', 'form button[type=submit]');
        $I->dontSeeLink('Login');
        $I->dontSeeLink('Signup');
    }
}
