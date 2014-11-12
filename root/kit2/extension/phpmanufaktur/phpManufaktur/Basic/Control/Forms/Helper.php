<?php

namespace phpManufaktur\Basic\Control\Forms;

use Silex\Application;

class Helper
{

    /**
     *
     * @access private
     * @return
     **/
    public static function build(Application $app, $fields)
    {
        // get form builder
        $form = $app['form.factory']->createBuilder('form');

        // global options
        $opt  = array();

        if(isset($config["pattern"]["form"]["create"]["attr"]))
            $opt['attr'] = $config["pattern"]["form"]["create"]["attr"];

        // populate the form
        foreach($fields as $field)
        {
            $field_opt = $opt;

            if(isset($field['type']) && !in_array($field['type'], array( 'submit','button' )))
            {
                $field_opt['label_attr'] = array('class'=>'col-sm-2 control-label');
                $field_opt['required']   = false;
            }

            if(isset($field['required'])) $field_opt['required'] = $field['required'];

            if(isset($field['label_attr']))
                $field_opt['label_attr']
                    = array_merge(
                        ( isset($field_opt['label_attr']) ? $field_opt['label_attr'] : array() ),
                        $field['label_attr']
                      );

            if(isset($field['constraints']))
            {
                $field_opt['constraints'] = array();
                foreach($field['constraints'] as $key => $opts)
                {
                    foreach($opts as $k => $v)
                    {
                        if(preg_match('~message~i',$k))
                        {
                            $opts[$k] = $app['translator']->trans($v);
                        }
                    }
                    $assert = 'Symfony\\Component\\Validator\\Constraints\\'.$key;
                    $field_opt['constraints'][] = new $assert($opts);
                }
            }

            $form->add(
                $field['name'],
                (isset($field['type']) ? $field['type'] : NULL),
                $field_opt
            );
        }

        return $form;

    }   // end function build()

    /**
     *
     * @access public
     * @return
     **/
    public static function getErrorsAsString($form)
    {
        $errors = preg_split('~([A-Z]+\:)~',(string) $form->getErrors(true), -1, PREG_SPLIT_NO_EMPTY);
        $return = array();
        foreach($errors as $err)
        {
            $return[] = trim($err);
        }
        return implode('<br />', $return);
    }   // end function getErrorsAsString()
    

}