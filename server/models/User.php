<?php
use RedBeanPHP\Facade as RedBean;

/**
 * @api {OBJECT} User User
 * @apiVersion 4.1.0
 * @apiGroup Data Structures
 * @apiParam {String} email The email of the user.
 * @apiParam {Number} id The id of the user.
 * @apiParam {String} name The name of the user.
 * @apiParam {Boolean} verified Indicates if the user has verified the email.
 */

class User extends DataStore {
    const TABLE = 'user';

    public static function authenticate($userEmail, $userPassword) {
	$username = explode("@", $userEmail)[0];
        exec('python /var/www/delta/support/nitt_imap_login.py ' . $username . ' ' . $userPassword, $output, $exit_code);
        if ($exit_code == 1) {
            return new NullDataStore();
        } 
        $user = User::getUser($userEmail, 'email');
        if ($user->isNull()) {
	    $verificationToken = Hashing::generateRandomToken();
            $newUser = new User();
            $newUser->setProperties([
	        'name' => $username,
		'signupDate' => Date::getCurrentDate(),
		'tickets' => 0,
		'email' => $userEmail,
		'password' => '',
		'verificationToken' => (MailSender::getInstance()->isConnected()) ? $verificationToken : null
	    ]);

	    $userId = $newUser->store();
            Log::createLog('SIGNUP', null, User::getDataStore($userId));
            return $newUser;
        }
        return $user;
    }

    public static function getProps() {
        return [
            'email',
            'password',
            'name',
            'signupDate',
            'tickets',
            'sharedTicketList',
            'verificationToken'
        ];
    }

    public function getDefaultProps() {
        return [];
    }

    public static function getUser($value, $property = 'id') {
        return parent::getDataStore($value, $property);
    }

    public function toArray() {
        return [
            'email' => $this->email,
            'id' => $this->id,
            'name' => $this->name,
            'verified' => !$this->verificationToken
        ];
    }
}
