<?php
/**
* Extendable Bootstrap Menu class
* @package Wordpress
* @subpackage one-theme
* @since 1.2
* @author Matthew Hansen
*/

class Bootstrap_Navigation {

    public $inverse = false;
    public $container = false;
    public $sticky_top = false;
    public $menu_location = 'main';
    public $menu = null;
    public $pos_right = false;

    function __construct($data = array()) {
        add_action('wp_head', array($this, 'center_menu'));
        if(count($data) > 0) {
            foreach($data as $name => $value) {
                $this->$name = $value;
            }
        }
    }


    function menu_output() {
    ?>

        <div class="navbar navbar-default<?= ($this->inverse == true ? ' navbar-inverse' : ''); ($this->sticky_top == true ? ' navbar-fixed-top' : '');?>" role="navigation">
            <?php if($this->container == true){
                echo '<div class="container-fluid">';
                } ?>


                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <?php echo apply_filters('navbar_brand', ''); ?>
                </div>

                <?php

                $menu_pos = 'nav navbar-nav';
                if($this->pos_right == true){
                    $menu_pos = 'nav navbar-nav navbar-right';
                }

                ?>

                <div class="collapse navbar-collapse navbar-ex1-collapse">
                       <?php if ( has_nav_menu( $this->menu_location ) or (!empty($this->menu) and isset($this->menu)) ) {
                        wp_nav_menu( array(
                            'menu'       => ((!empty($this->menu) and isset($this->menu)) ? $this->menu : $this->menu_location),
                            'theme_location' => $this->menu_location,
                            'depth'      => 2,
                            'container'  => false,
                            'menu_class' => $menu_pos,
                            'fallback_cb' => 'wp_page_menu',
                            'walker' => new wp_bootstrap_navwalker()
                            )
                        );
                    } ?>


                        <?php do_action('navbar_right'); ?>

                    </div>
                    <?php if($this->container == true){
                        echo '</div>';
                    } ?>
                </div>
                <?php
    }

}
