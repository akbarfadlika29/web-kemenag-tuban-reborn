<?php

return [

    'groups' => [

        'identity' => [
            'label' => 'Identitas Website',
            'description' => 'Identitas utama website dan instansi.',

            'fields' => [

                'site_name' => [
                    'label' => 'Nama Website',
                    'type' => 'text',
                    'default' => 'WEB PPID',
                    'required' => true,
                    'rules' => [
                        'required',
                        'string',
                        'max:150',
                    ],
                ],

                'logo_media_id' => [
                    'label' => 'Logo Website',
                    'type' => 'media',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'integer',
                    ],
                ],

                'site_tagline' => [
                    'label' => 'Tagline Website',
                    'type' => 'text',
                    'default' => 'Pelayanan Informasi Publik',
                    'rules' => [
                        'nullable',
                        'string',
                        'max:200',
                    ],
                ],

                'institution_name' => [
                    'label' => 'Nama Instansi',
                    'type' => 'text',
                    'default' => 'Kementerian Agama Kabupaten Tuban',
                    'required' => true,
                    'rules' => [
                        'required',
                        'string',
                        'max:200',
                    ],
                ],

                'institution_short_name' => [
                    'label' => 'Nama Singkat Instansi',
                    'type' => 'text',
                    'default' => 'Kemenag Kabupaten Tuban',
                    'rules' => [
                        'nullable',
                        'string',
                        'max:150',
                    ],
                ],

            ],
        ],

        'contact' => [
            'label' => 'Kontak & Pelayanan',
            'description' => 'Informasi kontak resmi yang dapat ditampilkan kepada masyarakat.',

            'fields' => [

                'address' => [
                    'label' => 'Alamat',
                    'type' => 'textarea',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'string',
                        'max:1000',
                    ],
                ],

                'email' => [
                    'label' => 'Email',
                    'type' => 'email',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'email',
                        'max:200',
                    ],
                ],

                'phone' => [
                    'label' => 'Telepon',
                    'type' => 'text',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'string',
                        'max:50',
                    ],
                ],

                'whatsapp' => [
                    'label' => 'WhatsApp Layanan',
                    'type' => 'text',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'string',
                        'max:50',
                    ],
                ],

                'service_hours' => [
                    'label' => 'Jam Pelayanan',
                    'type' => 'textarea',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'string',
                        'max:1000',
                    ],
                ],

            ],
        ],

        'social' => [
            'label' => 'Media Sosial',
            'description' => 'Tautan akun dan kanal media sosial resmi instansi. Kosongkan jika tidak digunakan.',

            'fields' => [

                'instagram_url' => [
                    'label' => 'Instagram',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

                'youtube_url' => [
                    'label' => 'YouTube',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

                'tiktok_url' => [
                    'label' => 'TikTok',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

                'facebook_url' => [
                    'label' => 'Facebook',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

                'twitter_url' => [
                    'label' => 'X / Twitter',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

                'threads_url' => [
                    'label' => 'Threads',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

                'linkedin_url' => [
                    'label' => 'LinkedIn',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

                'whatsapp_channel_url' => [
                    'label' => 'WhatsApp Channel',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

                'telegram_url' => [
                    'label' => 'Telegram',
                    'type' => 'url',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'url',
                        'max:2048',
                    ],
                ],

            ],
        ],

        'seo' => [
            'label' => 'SEO Default',
            'description' => 'Metadata bawaan ketika halaman tidak memiliki metadata khusus.',

            'fields' => [

                'default_meta_title' => [
                    'label' => 'Judul SEO Default',
                    'type' => 'text',
                    'default' => 'WEB PPID - Kementerian Agama Kabupaten Tuban',
                    'rules' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],
                ],

                'default_meta_description' => [
                    'label' => 'Deskripsi SEO Default',
                    'type' => 'textarea',
                    'default' => '',
                    'rules' => [
                        'nullable',
                        'string',
                        'max:500',
                    ],
                ],

            ],
        ],

    ],

];
