<?php
/*
Plugin Name: Orbisius Random Name Generator
Plugin URI: https://orbisius.com/products/wordpress-plugins/orbisius-random-name-generator
Description: Displays a random name out of words that you have specified between the shortcode [orbisius_random_name_generator]....[/orbisius_random_name_generator]
Version: 1.0.0
Author: Svetoslav Marinov (Slavi)
Author URI: https://orbisius.com
Text Domain: orbisius-tutorial-random-name-generator
Domain Path: /lang
*/

/*  Copyright 2012-2050 Svetoslav Marinov (Slavi) <slavi@orbisius.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$instance = Orbisius_Random_Name_Generator_Shortcodes::getInstance();
add_action( 'init', array( $instance, 'init' ) );

class Orbisius_Random_Name_Generator_Shortcodes {
	/**
	 *
	 */
	public function init() {
		add_shortcode( 'orbisius_random_name_generator', [ $this, 'renderForm' ], 20, 2 );
	}

	public function isPost() {
		return !empty($_POST) || (!empty($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0);
    }

	/**
     * Parses the content and strips the HTML before splitting on new lines and/or separators
	 * @param string $content
	 * @return array
	 */
	public function parseKeywords($content) {
		$content = is_scalar($content) ? $content : json_encode($content);
		$content = str_ireplace([ "<br/>", '<br>', "<p>", "</p>", "<div>", "</div>", ] , "\n", $content);
		$content = strip_tags($content);
		$content = trim($content);
		$words = preg_split('#[\t\r\n,;|]+#si', $content);
		$words = array_map('trim', $words);
		$words = array_unique($words);
		$words = array_filter($words);
		$words = empty($words) ? [] : $words;
		return $words;
    }

	/**
	 * Processes [orbisius_random_name_generator]....[/orbisius_random_name_generator]
	 * @param array $attribs
	 * @param string $content the text between the tags
	 * @return string
	 */
	public function renderForm($attribs = [], $content = '') {
		static $instance = 0; // in case it's used multiple times on a page
		$msg = '';
		ob_start();

		$instance++;

		// Let's get a random word only on post
		if ($this->isPost()) {
			$words = $this->parseKeywords($content);

			if (!empty($attribs['words'])) {
				$inp_words = $this->parseKeywords($attribs['words']);
				$words = array_replace_recursive($inp_words, $words);
				$words = array_unique($words);
			}

			shuffle($words);
			$word = reset($words);
			$msg = $word;
		}
		?>
		<div id="orbisius_random_name_generator_container orbisius_random_name_generator_container<?php echo $instance;?>"
		     class="orbisius_random_name_generator_container orbisius_random_name_generator_container<?php echo $instance;?>">
			<div class="row">
				<!-- Contact Form -->
				<div id="orbisius_random_name_generator_form_wrapper" class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
					<form id='orbisius_random_name_generator_form<?php echo $instance;?>'
					      class="orbisius_random_name_generator_form orbisius_random_name_generator_form<?php echo $instance;?> form-horizontal" method="POST">
                        <input type="hidden" name="instance_id" value="<?php echo $instance;?>">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<?php echo $msg; ?>
							</div>
						</div>
						<br/>
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<button id="submit" class="btn btn-color">
									<?php _e('Generate', 'orbisius-tutorial-random-name-generator');?></button>
							</div>
						</div>

						<div class="result app_hide"></div>
					</form>
				</div> <!-- /orbisius_random_name_generator_form_wrapper -->
			</div> <!-- /row -->
		</div> <!-- /orbisius_random_name_generator_container -->

		<?php
		$buff = ob_get_clean();
		$buff = trim($buff);
		return $buff;
	}

	/**
	 * Singleton pattern i.e. we have only one instance of this obj
	 * @staticvar static $instance
	 * @return static
	 */
	public static function getInstance() {
		static $instance = null;

		// This will make the calling class to be instantiated.
		// no need each sub class to define this method.
		if ( is_null( $instance ) ) {
			$instance = new static();
		}

		return $instance;
	}
}
