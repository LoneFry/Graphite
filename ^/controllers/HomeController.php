<?php
/**
 * Home Controller - A default Home and Contact page Controller class
 * File : /^/controllers/HomeController.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * HomeController class - A default Home and Contact page Controller class
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Controller.php
 */
class HomeController extends Controller {
    protected $action = 'home';

    /**
     * Display default Home page
     *
     * @return mixed
     */
    public function do_home() {
        G::$V->_template = 'Home.php';
        G::$V->_title    = G::$V->_siteName;
    }

    /**
     * Display contact form
     *
     * @param array $argv web request parameters
     * @param array $post Post request variable.
     *
     * @return mixed
     */
    public function do_contact($argv, $post) {
        G::$V->_template = 'Home.Contact.php';
        G::$V->_title    = G::$V->_siteName.': Contact';
        G::$V->seed      = $seed    = (int)(isset($post['apple'])
            ? $post['apple']
            : microtime(true));
        G::$V->from      = $from    = substr(md5($seed), -6);
        G::$V->subject   = $subject = md5($from);
        G::$V->message   = $message = md5($subject);
        G::$V->honey     = $honey   = md5($message);
        G::$V->honey2    = $honey2  = md5($honey);
        G::$V->_head .= '
        <style type="text/css">
            .c'.$honey.' {display:none;}
        </style>
';

        if (isset($post[$from])
            && isset($post[$subject])
            && isset($post[$message])
            && isset($post[$honey])
            && isset($post[$honey2])
        ) {
            $loginname = G::$S->Login?G::$S->Login->loginname:'[not logged in]';
            $login_id  = G::$S->Login?G::$S->Login->login_id:0;
            if ('' != $post[$honey] || '' != $post[$honey2]) {
                G::msg(Locallizer::translate('home.contact.msg.honeynotempty'));
            } elseif (false !== strpos($post[$from], "\n") || false !== strpos($post[$from], "\r")) {
                G::msg(Localizer::translate('home.contact.msg.fromnewline'));
            } elseif (false !== strpos($post[$subject], "\n") || false !== strpos($post[$subject], "\r")) {
                G::msg(Localizer::translate('home.contact.msg.subjectnewline'));
            } else {
                mail(G::$G['siteEmail'], G::$G['contactFormSubject'].$post[$subject],
                    'Login Info: '.$loginname.' - '.$login_id."\n"
                    .'Specified Email Address: '.$post[$from]."\n"
                    .'Subject: '.$post[$subject]."\n"
                    .'Message: '."\n".$post[$message],
                    'From: "'.G::$G['VIEW']['_siteName'].'" <'.G::$G['siteEmail'].">\n"
                    ."Reply-To: ".$post[$from]."\nX-Mailer: PHP/" . phpversion()
                    );
                G::msg(Localizer::translate('home.contact.msg.sent'));
                $C = new ContactLog(array(
                    'from'     => $post[$from],
                    'subject'  => $post[$subject],
                    'to'       => G::$G['siteEmail'],
                    'body'     => $post[$message],
                    'login_id' => $login_id,
                ), true);
                $C->save();
            }
        } else {
            G::msg(Localizer::translate('home.contact.msg.useform'));
        }
    }

    /**
     * Display log of submissions to the contact form
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_contactLog($argv) {
        if (!G::$S->roleTest('Home/ContactLog')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Home.ContactLog.php';
        G::$V->_title    = G::$V->_siteName.': Contact Log';

        G::$V->log = ContactLog::some(100, 0, 'id', true);
    }
}
