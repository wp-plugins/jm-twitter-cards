=== JM Twitter Cards ===
Contributors: jmlapam
Tags: twitter, cards, semantic markup, metabox
Requires at least: 
Tested up to: 3.5
License: GPLv2 or later
Stable tag: trunk
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin meant to simplify Twitter cards integration on WordPress. But this is more than just adding markup. You can customize your Twitter card experience :

== Description ==

Once activated the plugin adds Twitter cards on your posts according to your settings. Enjoy !
I created this plugin to provide the same Twitter card feature as SEO by Yoast plugin does but for those who do not use it.
But my plugin goes further...

Last update (1.1.7) includes multiple options. You can have different card on your website according to your settings. This is done with a simple metabox on posts, pages and attachments (edit post).

1.1.7 need PHP 5.3++ to works in full custom mode

<a href="http://twitter.com/tweetpressfr">Follow me on Twitter</a>

––––
En Français 
–––––––––––––––––––––––––––––––––––

Une fois activé le plugin s'occupe d'ajouter une card Twitter sur vos posts selon vos réglages. Profitez-en bien !
J'ai créé ce plugin pour tous les non utilisateurs de Seo by Yoast qui souhaitent avoir la possibilité d'insérer les Twitter Cards sans avoir à coder.
Mais le plugin va encore plus loin...

La dernière mise à jour (1.1.7) inclue de multiples options : vous pouvez avoir différentes type de card sur votre site, à paramétrer via une métabox qui s'ajoute dans l'administration sur les articles, les pages et les médias.

1.1.7 nécessite PHP 5.3 au minimum pour fonctionner en mode custom

<a href="http://twitter.com/tweetpressfr">Me suivre sur Twitter</a>

== Installation ==

1. Upload plugin files to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Then go to settings > JM Twitter Cards to configure the plugin
4. To display the metabox in edit section (posts, pages, attachments), enable option in section called **Custom Twitter Cards**

––––
En Français 
–––––––––––––––––––––––––––––––––––

1. Chargez les fichiers de l'archive dans le dossier /wp-content/plugins/ 
2. Activez le plugin dans le menu extensions de WordPress
3. Allez dans réglages > JM Twitter Cards pour configurer le plugin
4. Pour afficher la metabox dans vos admin de posts, de pages et de médias, activez l'option correspondante dans **Custom Twitter Cards**

== Frequently asked questions ==

= I got problem with Instagram = 
It's a known issue due to Instagram. Twitter said it recently :
*Users are experiencing issues with viewing Instagram photos on Twitter. 
Issues include cropped images. 
This is due to Instagram disabling its Twitter cards integration, and as a result, photos are being displayed using a pre-cards experience. 
So, when users click on Tweets with an Instagram link, photos appear cropped.*

= Plugin is fine but Twitter cards doesn't appear in my tweets =
1. Make sure you correctly fulfilled fields in option page according to <a href="https://dev.twitter.com/docs/cards" title="Twitter cards documentation">Twitter documentation</a>
2. Make sure you have correctly <a href="https://dev.twitter.com/node/7940" title="Twitter cards submit">submitted your website to Twitter</a>
3. Wait for Twitter's answer (a mail that tells you your site has been approved)
4. If it still doesn't work please open a thread on support or at this URL: <a href="http://tweetpress.fr/en/plugin/jm-twitter-cards">TweetPress, JM Twitter Cards</a>

––––
En Français 
–––––––––––––––––––––––––––––––––––

= Problème avec Instagram = 
C'est un problème connu. Cela vient d'Instagram lui-même qui préfère que ses utilisateurs partagent les photos chez lui plutôt que sur Twitter. Instagram a supprimé ses Twitter Cards.

= Le plugin marche mais je n'obtiens pas de Twitter Cards dans mes tweets =
1. Assurez-vous bien d'avoir rempli correctement les champs dans la page d'options suivant <a href="https://dev.twitter.com/docs/cards" title="Twitter cards documentation">la documentation Twitter</a>
2. Assurez-vous bien d'avoir <a href="https://dev.twitter.com/node/7940" title="Twitter cards formulaire de validation">soumis votre site à Twitter</a>
3. Attendez la réponse de Twitter (un mail qui vous indique que votre site a été approuvé)
4. Si cela ne marche toujours pas SVP ouvrez un topic sur le support du plugin ou à cette adresse : <a href="http://tweetpress.fr/plugin/jm-twitter-cards">TweetPress, JM Twitter Cards</a>

== Screenshots ==
1. admin
2. confirmation mail
3. metabox

== Changelog ==

= 1.1.7 =
* 17 jan 2013
* add WP Custom-fields by GeekPress. Code is cleaner.

= 1.1.6 =
* 28 dec 2012
* fixed bug with empty args on function get_post_by_id, put html out of my translation and remove esc_html for esc_attr (silly mistakes and thanks Juliobox for your comment), next update will include lighter code to integrate metabox

= 1.1.5 =
* 27 dec 2012
* add warnings for WP SEO by Yoast users

= 1.1.4 =
* 25 dec 2012
* add features and extra options

= 1.1.3 =
* 22 dec 2012
* fix bug with photo cards and add options

= 1.1.2 =
* 22 dec 2012
* twitter cards only single posts, next updates will offer more options

= 1.1.1 =
* 22 dec 2012
* add links and styles on admin to improve readability

= 1.1 =
* 6 dec 2012
* add a function_exists() in case of the functions are already implemented in functions.php or anywhere else in your theme

= 1.0 =
* 5 dec 2012
* Initial release
* thanks GregLone for his great help regarding optimization

== Upgrade notice ==
Nothing
= 1.0 =


