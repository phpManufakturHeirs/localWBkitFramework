## kitFramework::TemplateTools

The **TemplateTools** help you to create powerful templates for [WebsiteBaker](http://websitebaker.org), [LEPTON CMS](http://lepton-cms.org) and [BlackCat CMS](http://blackcat-cms.org).

### Installation

1. Use the [kitFramework CMS Tool](https://github.com/phpManufaktur/kitFramework_CMS_Tool_WebLepCat/releases) to extend your Content Management System with the [kitFramework](https://github.com/phpManufaktur/kitFramework/wiki)
2. In the *kitFramework CMS Tool* select the *TemplateTools* and install this extension.

### Include and Start

If you start using the TemplateTools you must not rewrite your existing templates, just include the Tools and use one or more of the additional features in any way you want.

Using the TemplateTools is easy, just include them at the top of the `index.php` which belong to the template you are working on:

    require_once WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/TemplateTools/initialize.php';
    
Now you have access to around hundred [[Constants]] ready to use, for example:

    echo CMS_TYPE.' - '.CMS_VERSION;
    
will prompt something like `WebsiteBaker - 2.8.3` or `BlackCat - 1.0.2` depending on the Content Management System your are using.  

The TemplateTools provide you also with the `php` variable `$template` to access to the [TemplateTools Services](https://github.com/phpManufaktur/kfTemplateTools/wiki/Services), for example to use the [Twig Template Engine](https://github.com/phpManufaktur/kfTemplateTools/wiki/Twig-Service) or a [Translator Service](https://github.com/phpManufaktur/kfTemplateTools/wiki/Translator-Service):

    echo $template['translator']->trans('Welcome back, %name%', 
        array('%name%' => CMS_USER_DISPLAYNAME));
        
this will prompt at a English (`en`) localized page something like:

    Welcome back, Ralf Hertsch
    
and at a German (`de`) localized page something like:

    Herzlich willkommen, Ralf Hertsch
    
Have a [look to the different Services](https://github.com/phpManufaktur/kfTemplateTools/wiki/Services) provided by the TemplateTools and test the [Template Examples](https://github.com/phpManufaktur/kfTemplateTools/wiki/Examples).

![TemplateTools](https://piwik.phpmanufaktur.de/piwik.php?idsite=15&rec=1)
<img src="https://piwik.phpmanufaktur.de/piwik.php?idsite=15&rec=1" alt="Piwik Counter" width="1" height="1" />
