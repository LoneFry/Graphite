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
    /** @var string Default action */
    protected $action = 'home';

    /**
     * Constructor
     *
     * @param array $argv Argument list passed from Dispatcher
     */
    public function __construct(array $argv = array()) {
        parent::__construct($argv);
    }

    /**
     * Display default Home page
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_home(array $argv = array(), array $request = array()) {
        G::$V->_template = 'Home.php';
        G::$V->_title    = G::$V->_siteName;
    }

    /**
     * Display contact form
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_contact(array $argv = array(), array $request = array()) {
        G::$V->_template = 'Home.Contact.php';
        G::$V->_title    = G::$V->_siteName.': Contact';
        G::$V->seed      = $seed    = (int)(isset($request['apple'])
            ? $request['apple']
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
        G::$V->_script('/^/js/validate-email.min.js');

        if (isset($request[$from])
            && isset($request[$subject])
            && isset($request[$message])
            && isset($request[$honey])
            && isset($request[$honey2])
        ) {
            if ('' != $request[$honey] || '' != $request[$honey2]) {
                G::msg(G::_('home.contact.msg.honeynotempty'));
            } elseif (false !== strpos($request[$from], "\n")
                || false !== strpos($request[$from], "\r")
            ) {
                G::msg(G::_('home.contact.msg.fromnewline'));
            } elseif (false !== strpos($request[$subject], "\n")
                || false !== strpos($request[$subject], "\r")
            ) {
                G::msg(G::_('home.contact.msg.subjectnewline'));
            } else {
                $loginname = G::$S->Login?G::$S->Login->loginname:'[not logged in]';
                $login_id  = G::$S->Login?G::$S->Login->login_id:0;

                $this->mailer($request, $from, $subject, $message,
                              $loginname, $login_id);
                G::msg(G::_('home.contact.msg.sent'));

                $ContactLog = new ContactLog(array(
                    'from'     => $request[$from],
                    'subject'  => $request[$subject],
                    'to'       => G::$G['siteEmail'],
                    'body'     => $request[$message],
                    'login_id' => $login_id,
                ), true);
                $ContactLog->save();
            }
        } else {
            G::msg(G::_('home.contact.msg.useform'));
        }
    }


    /**
     * Display log of submissions to the contact form
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_contactLog(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Home/ContactLog')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Home.ContactLog.php';
        G::$V->_title    = G::$V->_siteName.': Contact Log';

        G::$V->log = ContactLog::some(100, 0, 'id', true);
    }

    /**
     * Send message from Contact form displayed by do_contact()
     *
     * @param array  $post      Post Array
     * @param string $from      From Key
     * @param string $subject   Subject Key
     * @param string $message   Message Key
     * @param string $loginName User's login
     * @param string $loginId   Users' ID
     *
     * @return mixed
     */
    private function mailer($post, $from, $subject, $message,
                            $loginName, $loginId) {
        mail(G::$G['siteEmail'], G::$G['contactFormSubject'].$post[$subject],
            'Login Info: '.$loginName.' - '.$loginId."\n"
            .'Specified Email Address: '.$post[$from]."\n"
            .'Subject: '.$post[$subject]."\n"
            .'Message: '."\n".$post[$message],
            'From: "'.G::$G['VIEW']['_siteName'].'" <'.G::$G['siteEmail'].">\n"
            ."Reply-To: ".$post[$from]."\nX-Mailer: PHP/" . phpversion()
        );
    }
}
