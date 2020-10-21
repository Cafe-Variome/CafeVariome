<?php
namespace Config;

/**
 * AuthAdapter.php
 * Created 24/07/2019
 * @author Mehdi Mehtarizadeh
 * 
 * This file contains configuration for AuthAdapter class.
 * 
 */

class AuthAdapter extends \CodeIgniter\Config\BaseConfig
{

    /**
     * Valid values:
     *  1. KeyCloakFirst : If keycloak URI is configured properly and an endpoint exists,
     *  Keycloak is used as authentication engine. Otherwise, IonAuth is used.
     *  2. KeyCloakOnly
     *  3. IonAuthOnly
     *  4. OAuth
     */
    public $authRoutine = 'KeyCloakFirst';
}
