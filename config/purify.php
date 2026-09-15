<?php

use Stevebauman\Purify\Definitions\Html5Definition;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Config
    |--------------------------------------------------------------------------
    */

    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Config sets
    |--------------------------------------------------------------------------
    */

    'configs' => [

        'default' => [
            'Core.Encoding' => 'utf-8',

            'HTML.Doctype' =>
                'HTML 4.01 Transitional',

            'HTML.Allowed' =>
                'h1,h2,h3,h4,h5,h6,b,u,strong,i,em,s,del,a[href|title],ul,ol,li,p[style],br,span,img[width|height|alt|src],blockquote',

            'HTML.ForbiddenElements' =>
                '',

            'CSS.AllowedProperties' =>
                'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',

            'AutoFormat.AutoParagraph' =>
                false,

            'AutoFormat.RemoveEmpty' =>
                false,
        ],

        /*
         * Config khusus konten berita.
         */
        'news' => [
            'Core.Encoding' =>
                'utf-8',

            'HTML.Doctype' =>
                'HTML 4.01 Transitional',

            'HTML.Allowed' => implode(',', [
                'h2[class|style]',
                'h3[class|style]',
                'h4[class|style]',
                'p[class|style]',
                'br',
                'strong',
                'b',
                'em',
                'i',
                'u',
                's',
                'blockquote[class|style]',
                'ul[class]',
                'ol[class]',
                'li[class]',
                'a[href|title|target|rel]',
                'img[src|alt|title|width|height|class]',
                'pre[class]',
                'code',
                'span[class|style]',
                'hr',
                'sup',
                'sub',
            ]),

            'HTML.ForbiddenElements' =>
                'script,iframe,object,embed,form,input,button,style',

            'CSS.AllowedProperties' => implode(',', [
                'text-align',
                'color',
                'background-color',
                'font-weight',
                'font-style',
                'text-decoration',
            ]),

            'Attr.AllowedFrameTargets' => [
                '_blank',
            ],

            'AutoFormat.AutoParagraph' =>
                false,

            'AutoFormat.RemoveEmpty' =>
                false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | HTMLPurifier definitions
    |--------------------------------------------------------------------------
    */

    'definitions' =>
        Html5Definition::class,

    /*
    |--------------------------------------------------------------------------
    | HTMLPurifier CSS definitions
    |--------------------------------------------------------------------------
    */

    'css-definitions' =>
        null,

    /*
    |--------------------------------------------------------------------------
    | Serializer
    |--------------------------------------------------------------------------
    */

    'serializer' => [
        'driver' =>
            env(
                'CACHE_STORE',
                env(
                    'CACHE_DRIVER',
                    'file'
                )
            ),

        'cache' =>
            \Stevebauman\Purify\Cache\CacheDefinitionCache::class,
    ],

];