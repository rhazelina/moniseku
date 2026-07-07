<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL (WAJIB SESUAI IP LOKAL)
     * --------------------------------------------------------------------------
     */
    public string $baseURL = 'https://gkibrmpatrol.my.id/';

    /**
     * Allowed Hostnames
     */
    public array $allowedHostnames = [];

    /**
     * Index File
     */
    public string $indexPage = 'index.php';

    /**
     * URI PROTOCOL
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * Allowed URL Characters
     */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * Default Locale
     */
    public string $defaultLocale = 'en';

    /**
     * Negotiate Locale
     */
    public bool $negotiateLocale = false;

    /**
     * Supported Locales
     */
    public array $supportedLocales = ['en'];

    /**
 * Application Timezone
 */
public string $appTimezone = 'Asia/Jakarta';

    /**
     * Default Character Set
     */
    public string $charset = 'UTF-8';

    /**
     * Force HTTPS
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * Reverse Proxy IPs
     */
    public array $proxyIPs = [];

    /**
     * Content Security Policy
     */
    public bool $CSPEnabled = false;
}