<?php 
	return [
        'blocks' => [
         'divider_preset' => [
           'name' => 'Home Presets',
           'type' => 'divider'
         ],

         'zoa_preset' => [
              'name' => 'Zoa Preset',
              'type' => 'normal',
              'icon' => 'Ecom Icon Grid 14.png',
              'banner' => 'bannerimg.png',
              'subtitle' => 'Basic store home preset',
              'inputs' => [
                'about_title' => ['type' => 'text', 'name' => 'About Title'],
                'short_about' => ['type' => 'textarea', 'name' => 'About short description'],
              ],
              'include' => 'zoa_preset_home',
          ],

         'fasion_preset_1' => [
              'name' => 'Fashion Preset',
              'type' => 'normal',
              'icon' => 'Ecom Icon Grid 14.png',
              'banner' => 'bannerimg.png',
              'subtitle' => 'Simple fashion homepage preset',
              'inputs' => [
                'about_title' => ['type' => 'text', 'name' => 'About Title'],
                'short_about' => ['type' => 'textarea', 'name' => 'About short description'],
              ],
              'include' => 'fashion-preset.fashion_preset_home',
          ],

         'enhanced_preset' => [
              'name' => 'Enhanced Preset',
              'type' => 'normal',
              'icon' => 'Ecom Icon Grid 14.png',
              'banner' => 'bannerimg.png',
              'subtitle' => 'Elegant and minimal homepage preset with special blocks',
              'inputs' => [
                'banner' => ['type' => 'image', 'name' => 'Banner'],
                'banner_subtitle' => ['type' => 'textarea', 'name' => 'Banner Subtitle'],
                'about_banner' => ['type' => 'image', 'name' => 'About section banner'],
                'short_about' => ['type' => 'textarea', 'name' => 'About short description'],
              ],
              'include' => 'enhanced-preset.enhanced_preset_home',
          ],

         'divider_basic' => [
           'name' => 'Basic',
           'type' => 'divider'
         ],

          'page_title' => [
            'name' => 'Page Title',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 1.png',
            'banner' => 'bannerimg.png',
            'subtitle' => 'Add Heading Tag',
            'inputs' => [
              'title' => ['type' => 'text', 'name' => 'Title'],
            ],
            'include' => 'pageTitle',
          ],

          'textarea' => [
            'name' => 'Text Area',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 2.png',
            'banner' => 'bannerimg.png',
            'subtitle' => 'Add Simple Text',
            'inputs' => [
              'textarea' => ['type' => 'textarea', 'name' => 'Textarea'],
            ],
            'include' => 'textarea',
          ],

          'divider_b' => [
            'name' => 'Banners',
            'type' => 'divider'
          ],

          'products_banner_1' => [
            'name' => 'Products Banner 1',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 3.png',
            'banner' => 'bannerimg.png',
            'subtitle' => 'Products banner slider',
            'inputs' => [
              'number_of_products' => ['type' => 'select', 'name' => 'Number Of Products', 'options' => ['1' => '1', '2' => '2', '3' => '3']],
            ],
            'include' => 'banner',
          ],

          'personal_banner' => [
            'name' => 'Personal Banner 1',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 5.png',
            'banner' => 'bannerimg.png',
            'subtitle' => 'Personal banner',
            'inputs' => [
              'tagline' => ['type' => 'text', 'name' => 'Tagline'],
              'short_about' => ['type' => 'textarea', 'name' => 'A little description / About'],
              'banner' => ['type' => 'image', 'name' => 'Background image'],
            ],
            'include' => 'banner_2',
          ],



          'divider_1' => [
            'name' => 'About',
            'type' => 'divider'
          ],

          'left_banner_about' => [
            'name' => 'About section',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 4.png',
            'banner' => 'bannerimg.png',
            'subtitle' => 'Short about',
            'inputs' => [
              'title' => ['type' => 'text', 'name' => 'Title'],
              'description' => ['type' => 'textarea', 'name' => 'Description'],
            ],
            'include' => 'about_left_banner',
          ],

          'divider_2' => [
            'name' => 'Products',
            'type' => 'divider'
          ],

        'products' => [
            'name' => 'Products',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 13.png',
            'subtitle' => 'Products grid',
            'products' => ['limit' => 2],
            'banner' => 'bannerimg.png',
            'inputs' => [
              'number_of_products' => ['type' => 'select', 'name' => 'Number Of Products', 'options' => ['1' => '1', '5' => '5', '999' => 'All']],
              'show_search' => ['type' => 'select', 'name' => 'Show search form', 'options' => [0 => 'No', 1 => 'Yes']],
            ],
            'include' => 'product',
        ],

        'product_categories_1' => [
            'name' => 'Products Categories',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 13.png',
            'subtitle' => 'Big Products Categories',
            'banner' => 'bannerimg.png',
            'inputs' => [
              'number_of_products' => ['type' => 'select', 'name' => 'Number Of Products', 'options' => ['3' => '3', '6' => '6', '999' => 'All']],
            ],
            'include' => 'product_categories_1',
        ],

       'product_categories_2' => [
            'name' => 'Products Categories 2',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 13.png',
            'banner' => 'bannerimg.png',
            'subtitle' => 'Small Products Categories',
            'inputs' => [
              'number_of_products' => ['type' => 'select', 'name' => 'Number Of Products', 'options' => ['4' => '4', '8' => '8', '999' => 'All']],
            ],
            'include' => 'product_categories_2',
       ],
       'divider_blog' => [
         'name' => 'Blog',
         'type' => 'divider'
       ],
       'blogs_list' => [
            'name' => 'Blog list',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 12.png',
            'banner' => 'bannerimg.png',
            'subtitle' => 'List blog posts',
            'inputs' => [
              'number_of_blogs' => ['type' => 'select', 'name' => 'Number Of Blog', 'options' => ['3' => '3', '6' => '6', '999' => 'All']],
            ],
            'include' => 'blogs.blog_list',
       ],
       'blogs_grid' => [
            'name' => 'Blog Grid',
            'type' => 'normal',
            'icon' => 'Ecom Icon Grid 13.png',
            'banner' => 'bannerimg.png',
            'subtitle' => 'Grid of blog posts',
            'inputs' => [
              'number_of_blogs' => ['type' => 'select', 'name' => 'Number Of Blog', 'options' => ['3' => '3', '6' => '6', '999' => 'All']],
            ],
            'include' => 'blogs.blog_grid',
       ],
    ],


    'pages' => [
      'home' => [
        'name' => 'Home',
        'active' => 1,
        'slug' => 'home',
        'blocks' => [
          'enhanced_preset' => [
            'banner' => ['type' => 'image', 'value' => ''],
            'banner_subtitle' => ['type' => 'textarea', 'value' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s"],

            'about_banner' => ['type' => 'image', 'value' => ''],

            'short_about' => ['type' => 'textarea', 'value' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."],
          ],
        ],
      ],
      'products' => [
        'name' => 'Products',
        'active' => 0,
        'slug' => 'products',
        'blocks' => [
          'products' => [
            'number_of_products' => ['type' => 'select', 'value' => 999],
            'show_search' => ['type' => 'select', 'value' => 1],
          ],
        ],
      ],
    ],
	];
?>