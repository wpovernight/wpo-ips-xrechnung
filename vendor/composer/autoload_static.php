<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdc00fe943bfca8633eab23427e252a8b
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPO\\IPS\\EN16931\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPO\\IPS\\EN16931\\' => 
        array (
            0 => __DIR__ . '/../..' . '/en16931',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'WPO\\IPS\\EN16931\\Handlers\\Common\\AddressHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Common/AddressHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Common\\CustomizationIdHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Common/CustomizationIdHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Common\\DocumentCurrencyCodeHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Common/DocumentCurrencyCodeHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Common\\DueDateHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Common/DueDateHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Common\\LegalMonetaryTotalHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Common/LegalMonetaryTotalHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Common\\PaymentTermsHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Common/PaymentTermsHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Common\\ProfileIdHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Common/ProfileIdHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Common\\TaxTotalHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Common/TaxTotalHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Invoice\\InvoiceLineHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Invoice/InvoiceLineHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Invoice\\InvoiceNoteHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Invoice/InvoiceNoteHandler.php',
        'WPO\\IPS\\EN16931\\Handlers\\Invoice\\InvoiceTypeCodeHandler' => __DIR__ . '/../..' . '/en16931/Handlers/Invoice/InvoiceTypeCodeHandler.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdc00fe943bfca8633eab23427e252a8b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdc00fe943bfca8633eab23427e252a8b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdc00fe943bfca8633eab23427e252a8b::$classMap;

        }, null, ClassLoader::class);
    }
}
