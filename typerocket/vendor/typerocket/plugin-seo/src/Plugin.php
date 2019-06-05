<?php
namespace TypeRocketSEO;

class Plugin
{
    public $itemId = null;
    public $version = '4.1';
    public $optionsName = 'tr_seo_options';
    public $postTypes = null;
    public $title = null;

    public function __construct()
    {
        if ( ! function_exists( 'add_action' ) ) {
            echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
            exit;
        }

        add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );
        add_action( 'typerocket_loaded', [$this, 'setup']);

    }

    public function setup()
    {
        if ( ! defined( 'WPSEO_URL' ) && ! defined( 'AIOSEOP_VERSION' ) ) {
            $this->postTypes = apply_filters('tr_seo_post_types', $this->postTypes);
            define( 'TR_SEO', $this->version );
            $this->optionsName = apply_filters( 'tr_seo_options_name', $this->optionsName );
            add_action('tr_model', [$this, 'fillable'], 9999999999, 2 );
            add_action( 'wp_head', [$this, 'head_data'], 1 );
            add_action( 'template_redirect', [$this, 'loaded'], 0 );
            add_filter( 'document_title_parts', [$this, 'title'], 100, 3 );
            remove_action( 'wp_head', 'rel_canonical' );
            add_action( 'wp', [$this, 'redirect'], 99, 1 );
            add_action('admin_menu', [$this, 'registerPage']);


            if ( is_admin() ) {
                add_action( 'add_meta_boxes', [$this, 'seo_meta']);
            }
        }
    }

    public function registerPage()
    {
        if(apply_filters('tr_seo_options_page', true)) {
            add_options_page( 'SEO Options', 'SEO Options', 'manage_options', 'tr_seo_options', [$this, 'page']);
        }
    }

    public function page()
    {
        do_action('tr_theme_options_page', $this);
        echo '<div class="wrap">';
        include( __DIR__ . '/../page.php' );
        echo '</div>';
    }

    public function fillable( $model )
    {
        global $post;

        if($model instanceof \TypeRocket\Models\WPPost) {
            $fillable = $model->getFillableFields();
            /** @var \WP_Post $data */
            $types = get_post_types(['public' => true]);
            if(!empty($fillable) && !empty($types[$post->post_type]) ) {
                $model->appendFillableField('seo');
            }
        } elseif ($model instanceof \TypeRocket\Models\WPOption) {
            $fillable = $model->getFillableFields();

            if ( ! empty( $fillable )) {
                $model->appendFillableField( $this->optionsName );
            }
        }
    }

    public function loaded()
    {
        $this->itemId = get_queried_object_id();
    }

    public function seo_meta()
    {
        $publicTypes = $this->postTypes ?? get_post_types( ['public' => true] );
        $args        = [
            'label'    => __('Search Engine Optimization'),
            'priority' => 'low',
            'callback' => [$this, 'meta']
        ];
        $obj         = new \TypeRocket\Register\MetaBox( 'tr_seo', null, $args );
        $obj->addPostType( $publicTypes )->register();
    }

    // Page Title
    public function title( $title, $arg2 = null, $arg3 = null )
    {
        $newTitle = trim(tr_posts_field( 'seo.meta.title', $this->itemId ));

        if ( !empty($newTitle) ) {
            $this->title = $newTitle;
            return [$newTitle];
        } else {
            $this->title = $title;
            return $title;
        }

    }

    public function title_tag()
    {
        echo '<title>' . $this->title( '|', false, 'right' ) . "</title>";
    }

    public function getLastValidItem(array $options, $callback = 'esc_attr')
    {
        $result = null;
        foreach ($options as $option) {
            if(!empty($option)) {
                $value = call_user_func($callback, trim($option));

                if(!empty($value)) {
                    $result = $value;
                }
            }
        }

        return $result;
    }

    // head meta data
    public function head_data()
    {
        $object_id = (int) $this->itemId;

        // Vars
        $url              = get_the_permalink($object_id);
        $seo              = tr_posts_field('seo.meta', $object_id);
        $seo_global       = get_option($this->optionsName);
        $desc             = esc_attr( $seo['description'] );

        // Images
        $img              = !empty($seo['meta_img']) ? wp_get_attachment_image_src( (int) $seo['meta_img'], 'full')[0] : null;

        // Basic
        $basicMeta['description'] = $desc;

        // OG
        $ogMeta['og:locale']      = $seo_global['og']['locale'] ?? null;
        $ogMeta['og:site_name']   = $seo_global['og']['site_name'] ?? null;
        $ogMeta['og:type']        = $this->getLastValidItem([ is_front_page() ? 'website' : 'article' , $seo['og_type'] ]);
        $ogMeta['og:title']       = esc_attr( $seo['og_title'] );
        $ogMeta['og:description'] = esc_attr( $seo['og_desc'] );
        $ogMeta['og:url']         = $url;
        $ogMeta['og:image']       = $img;

        // Canonical
        $canon            = esc_attr( $seo['canonical'] );

        // Robots
        $robots['index']  = esc_attr( $seo['index'] );
        $robots['follow'] = esc_attr( $seo['follow'] );

        $twMeta['twitter:card']        = esc_attr( $seo['tw_card'] );
        $twMeta['twitter:title']       = esc_attr( $seo['tw_title'] );
        $twMeta['twitter:description'] = esc_attr( $seo['tw_desc'] );
        $twMeta['twitter:site']        = $this->getLastValidItem([$seo_global['tw']['site'],$seo['tw_site']]);
        $twMeta['twitter:image']       = !empty($seo['tw_img']) ? wp_get_attachment_image_src( (int) $seo['tw_img'], 'full')[0] : null;
        $twMeta['twitter:creator']     = $this->getLastValidItem([$seo_global['tw']['creator'],$seo['tw_creator']]);

        // Basic
        foreach ($basicMeta as $basicName => $basicContent) {
            if(!empty($basicContent)) {
                echo "<meta name=\"{$basicName}\" content=\"{$basicContent}\" />";
            }
        }

        // Canonical
        if ( ! empty( $canon ) ) {
            echo "<link rel=\"canonical\" href=\"{$canon}\" />";
        } else {
            rel_canonical();
        }

        // Robots
        if ( ! empty( $robots ) ) {
            $robot_data = '';
            foreach ( $robots as $value ) {
                if ( ! empty( $value ) && $value != 'none' ) {
                    $robot_data .= $value . ', ';
                }
            }

            $robot_data = mb_substr( $robot_data, 0, - 2 );
            if ( ! empty( $robot_data ) ) {
                echo "<meta name=\"robots\" content=\"{$robot_data}\" />";
            }
        }

        // OG
        foreach ($ogMeta as $ogName => $ogContent) {
            if(!empty($ogContent)) {
                echo "<meta property=\"{$ogName}\" content=\"{$ogContent}\" />";
            }
        }

        // Twitter
        foreach ($twMeta as $twName => $twContent) {
            if(!empty($twContent)) {
                echo "<meta name=\"{$twName}\" content=\"{$twContent}\" />";
            }
        }

        $this->schemaJsonLd([
           'url' => $url,
           'description' => $desc,
           'og_global' => $seo_global,
        ]);
    }

    public function schemaJsonLd(array $data)
    {
        /** @var WP_Post $post */
        global $post;
        /**
         * @var $url
         * @var $og_global
         * @var $description
         */
        extract($data);

        if(empty($og_global)) { return; }

        $home = home_url();
        $lang = esc_js(str_replace('_', '-', $og_global['og']['locale']));
        $site = $og_global['og']['site_name'];
        $title = str_replace('&amp;', '&', esc_js($this->title));
        $desc = esc_js($description);

        // ISO 8601 Date Format
        $pub = get_the_date('c', $post);
        $mod = get_the_modified_date("c", $post);

        // Same As
        $same = array_map(function($value) {
            return esc_url_raw($value);
        }, $og_global['og']['social_links']);

        $schema_web = [
            "@context" => "http://schema.org/",
            "@graph"=> [
                    [
                        "@type"=>"Organization",
                        "@id"=>"$home#organization",
                        "name"=>"$site",
                        "url"=> "$home",
                        "sameAs"=> $same
                    ],
                    [
                        "@type"=>"WebSite",
                        "@id"=> "$home#website",
                        "url"=> "$home",
                        "name"=> "$site",
                        "publisher"=>  [
                        "@id"=> "$home#organization"
                        ]
                    ],
                    [
                        "@type"=> "WebPage",
                        "@id"=> "$url#webpage",
                        "url"=> "$url",
                        "inLanguage"=> "$lang",
                        "name"=> "$title",
                        "isPartOf"=> [ "@id"=> "$home/#website"],
                        "datePublished"=> "$pub",
                        "dateModified"=> "$mod",
                        "description"=> "$desc"
                    ]
                ]
            ];

        if($schema_web) {
            ?><script type="application/ld+json"><?php echo json_encode($schema_web); ?></script><?php
        }

        $biz = $og_global['schema']['enable'] ?? null;

        if($biz == '1') {
            $location = array_map('esc_js', $og_global['schema']['location']);
            $schema = array_map('esc_js', $og_global['schema']);
            $keyword = $schema['keyword'];
            $phone   = $schema['phone'];
            $price   = $schema['price_range'];

            $schema_biz = array_filter([
                "@context" => "http://schema.org/",
                "@type" => "ProfessionalService",
                "additionalType" => "http://www.productontology.org/id/$keyword",
                "url" => $home,
                "name" => $schema['name'],
                "description" => $schema['description'],
                "logo" => $schema['logo'] ? wp_get_attachment_image_src($schema['logo'], 'full')[0] : null,
                "image" => $schema['company_image'] ? wp_get_attachment_image_src($schema['company_image'], 'full')[0] : null,
                "telephone" => $phone,
                "priceRange" => $price,
                "address" => array_filter([
                    "@type" => "PostalAddress",
                    "addressLocality" => $location['city'],
                    "addressRegion" => $location['state'],
                    "addressCountry" => $location['country']
                ]),
                "sameAs"=> $same
            ]);

            ?><script type="application/ld+json"><?php echo json_encode($schema_biz); ?></script><?php
        }
    }

    // 301 Redirect
    public function redirect()
    {
        if ( is_singular() ) {
            $redirect = tr_posts_field( 'seo.meta.redirect', $this->itemId );
            if ( ! empty( $redirect ) ) {
                wp_redirect( $redirect, 301 );
                exit;
            }
        }
    }

    public function meta()
    {
        // build form
        $form = new \TypeRocket\Elements\Form();
        $form->setDebugStatus( false );
        $form->setGroup( 'seo.meta' );
        $seo_plugin = $this;

        // General
        $general = function() use ($form, $seo_plugin){

            $title = [
                'label' => __('Page Title')
            ];

            $desc = [
                'label' => __('Search Result Description')
            ];

            echo $form->text( 'title', ['id' => 'tr_title'], $title );
            echo $form->textarea( 'description', ['id' => 'tr_description'], $desc );

            $seo_plugin->general();
        };

        // Social
        $social = function() use ($form){

            $og_title = [
                'label' => __('Title'),
                'help'  => __('The open graph protocol is used by social networks like FB, Google+ and Pinterest. Set the title used when sharing.')
            ];

            $og_desc = [
                'label' => __('Description'),
                'help'  => __('Set the open graph description to override "Search Result Description". Will be used by FB, Google+ and Pinterest.')
            ];

            $og_type = [
                'label' => __('Page Type'),
                'help'  => __('Set the open graph page type. You can never go wrong with "Article".')
            ];

            $img = [
                'label' => __('Image'),
                'help'  => __("The image is shown when sharing socially using the open graph protocol. Will be used by FB, Google+ and Pinterest. Need help? Try the Facebook <a href=\"https://developers.facebook.com/tools/debug/og/object/\" target=\"_blank\">open graph object debugger</a> and <a href=\"https://developers.facebook.com/docs/sharing/best-practices\" target=\"_blank\">best practices</a>.")
            ];

            echo $form->text( 'og_title', [], $og_title );
            echo $form->textarea( 'og_desc', [], $og_desc );
            echo $form->select( 'og_type', [], $og_type )->setOptions(['Article' => 'article', 'Profile' => 'profile']);
            echo $form->image( 'meta_img', [], $img );
        };

        // Twitter
        $twitter = function() use ($form){

            $tw_img = [
                'label' => __('Image'),
                'help'  => __("Images for a 'summary_large_image' card should be at least 280px in width, and at least 150px in height. Image must be less than 1MB in size. Do not use a generic image such as your website logo, author photo, or other image that spans multiple pages.")
            ];

            $tw_help = __("Need help? Try the Twitter <a href=\"https://cards-dev.twitter.com/validator/\" target=\"_blank\">card validator</a>, <a href=\"https://dev.twitter.com/cards/getting-started\" target=\"_blank\">getting started guide</a>, and <a href=\"https://business.twitter.com/en/help/campaign-setup/advertiser-card-specifications.html\" target=\"_blank\">advertiser creative specifications</a>.");

            $card_opts = [
                __('Summary')             => 'summary',
                __('Summary large image') => 'summary_large_image',
            ];

            echo $form->text('tw_site')->setLabel('Site Twitter Account')->setAttribute('placeholder', '@username');
            echo $form->text('tw_creator')->setLabel('Page Author\'s Twitter Account')->setAttribute('placeholder', '@username');
            echo $form->select('tw_card')->setOptions($card_opts)->setLabel('Card Type')->setSetting('help', $tw_help);
            echo $form->text('tw_title')->setLabel('Title')->setAttribute('maxlength', 70 );
            echo $form->textarea('tw_desc')->setLabel('Description')->setHelp( __('Description length is dependent on card type.') );
            echo $form->image('tw_img', [], $tw_img );
        };

        // Advanced
        $advanced = function() use ($form){
            global $post;

            $link = esc_url_raw(get_permalink($post));

            $redirect = [
                'label'    => __('301 Redirect'),
                'help'     => __('Move this page permanently to a new URL.') . '<a href="#tr_redirect" id="tr_redirect_lock">' . __('Unlock 301 Redirect') .'</a>',
                'readonly' => true
            ];

            $follow = [
                'label' => __('Robots Follow?'),
                'desc'  => __("Don't Follow"),
                'help'  => __('This instructs search engines not to follow links on this page. This only applies to links on this page. It\'s entirely likely that a robot might find the same links on some other page and still arrive at your undesired page.')
            ];

            $follow_opts = [
                __('Not Set')      => 'none',
                __('Follow')       => 'follow',
                __("Don't Follow") => 'nofollow'
            ];

            $index_opts = [
                __('Not Set')     => 'none',
                __('Index')       => 'index',
                __("Don't Index") => 'noindex'
            ];

            $canon = [
                'label' => __('Canonical URL'),
                'help'  => __('The canonical URL that this page should point to, leave empty to default to permalink.')
            ];

            $help = [
                'label' => __('Robots Index?'),
                'desc'  => __("Don't Index"),
                'help'  => __('This instructs search engines not to show this page in its web search results.')
            ];

            echo $form->text( 'canonical', [], $canon );
            echo $form->text( 'redirect', ['readonly' => 'readonly', 'id' => 'tr_redirect'], $redirect );
            echo $form->row([
                $form->select( 'follow', [], $follow )->setOptions($follow_opts),
                $form->select( 'index', [], $help )->setOptions($index_opts)
            ]);

            $schema = "<a class=\"button\" href=\"https://search.google.com/structured-data/testing-tool/u/0/#url=$link\" target=\"_blank\">Analyze Schema</a>";
            $speed = "<a class=\"button\" href=\"https://developers.google.com/speed/pagespeed/insights/?url=$link\" target=\"_blank\">Analyze Page Speed</a>";

            echo $form->rowText('<div class="control-label"><span class="label">Google Tools</span></div><div class="control"><div class="button-group">'.$speed.$schema.'</div></div>');
        };

        $tabs = new \TypeRocket\Elements\Tabs();
        $tabs->addTab( [
            'id'       => 'seo-general',
            'title'    => __("Basic"),
            'callback' => $general
        ])
            ->addTab( [
                'id'      => 'seo-social',
                'title'   => __("Social"),
                'callback' => $social
            ])
            ->addTab( [
                'id'      => 'seo-twitter',
                'title'   => __("Twitter Cards"),
                'callback' => $twitter
            ])
            ->addTab( [
                'id'      => 'seo-advanced',
                'title'   => __("Advanced"),
                'callback' => $advanced
            ])
            ->render();
    }

    public function general()
    {
        global $post; ?>
        <div id="tr-seo-preview" class="control-group">
            <h4><?php _e('Example Preview'); ?></h4>

            <p><?php _e('Google has <b>no definitive character limits</b> for page "Titles" and "Descriptions". However, your Google search result may look something like:'); ?>

            <div class="tr-seo-preview-google">
        <span id="tr-seo-preview-google-title-orig">
          <?php echo mb_substr( strip_tags( $post->post_title ), 0, 59 ); ?>
        </span>
                <span id="tr-seo-preview-google-title">
          <?php
          $title = tr_posts_field( 'seo.meta.title' );
          if ( ! empty( $title ) ) {
              $s  = strip_tags( $title );
              $tl = mb_strlen( $s );
              echo mb_substr( $s, 0, 59 );
          } else {
              $s  = strip_tags( $post->post_title );
              $tl = mb_strlen( $s );
              echo mb_substr( $s, 0, 59 );
          }

          if ( $tl > 59 ) {
              echo '...';
          }
          ?>
        </span>

                <div id="tr-seo-preview-google-url">
                    <?php echo get_permalink( $post->ID ); ?>
                </div>
                <span id="tr-seo-preview-google-desc-orig">
          <?php echo mb_substr( strip_tags( $post->post_content ), 0, 150 ); ?>
        </span>
                <span id="tr-seo-preview-google-desc">
          <?php
          $desc = tr_posts_field( 'seo.meta.description' );
          if ( ! empty( $desc ) ) {
              $s  = strip_tags( $desc );
              $dl = mb_strlen( $s );
              echo mb_substr( $s, 0, 150 );
          } else {
              $s  = strip_tags( $post->post_content );
              $dl = mb_strlen( $s );
              echo mb_substr( $s, 0, 150 );
          }

          if ( $dl > 150 ) {
              echo ' ...';
          }
          ?>
        </span>
            </div>
        </div>
    <?php }
}