<?php
/*  DOCUMENTATION
    .............

    require('../../config.php');
	It loads all the Moodle core library by initialising the database connection, session, current course, theme and language.
	
	require_once($CFG->libdir.'/adminlib.php');
	states the functions and classes used during installation, upgrades and for admin settings.
	
	$path = optional_param('path', '', PARAM_PATH);
    $pageparams = array();
    if ($path) {
        $pageparams['path'] = $path;
    }
	In Moodle you can call or pass the parameters. As moodle_url doesn't provide you a way of generating the array, so you'll
	have to construct the params yourself. By defining your custom page to the function admin external page.
	
	Core global variables in Moodle are identified using uppercase variables (ie $CFG, $SESSION, $USER, $COURSE, $SITE, $PAGE,
	$DB and $THEME).
	$CFG: $CFG stands for configuration. This global variable contains configuration values of the Moodle setup, such as the
	root directory, data directory, database details, and other config values.
	
	$SESSION: Moodle's wrapper round PHP's $_SESSION.
	
    $USER: Holds the user table record for the current user. This will be the 'guest' user record for people who are not
	logged in.
	
	$SITE: Frontpage course record. This is the course record with id=1.
	
	$COURSE: This global variable holds the current course details. An alias for $PAGE->course.
	
	$PAGE: This is a central store of information about the current page we are generating in response to the user's request.
	ex: $PAGE->set_url('/mod/mymodulename/view.php', array('id' => $cm->id));
        $PAGE->set_title('My modules page title');
        $PAGE->set_heading('My modules page heading');

    $OUTPUT: $OUTPUT is an instance of core_renderer or one of its subclasses. It is used to generate HTML for output.
	ex: echo $OUTPUT->header();
	    echo $OUTPUT->heading($pagetitle);
		
	$CONTEXT: A context is combined with role permissions to define a User's capabilities on any page in Moodle.

    $DB: This holds the database connection details. It is used for all access to the database.

    $PAGE->set_url('/local/slack/userdata.php');
	Every moodle page needs page url through a call to $PAGE->set_url. You are trying to define the page url for setting the 
	custom page.
	
	require_login();
	It verifies that user is logged in before accessing any moodle page.
	
	$PAGE->set_pagelayout('admin'); Set a default pagelayout. 
	(or) 
    $PAGE->set_pagelayout('standard');
	When setting the page layout you should use the layout that is the closest match to the page you are creating. 
    Layouts are used by themes to determine what is shown on the page. There are different layouts that can be, and are used
    throughout Moodle core that you can use within your code. The list of common layouts you are best to look at
	theme/base/config.php or refer to the list below.
	
	It's important to know that the theme determines what layouts are available and how each looks. If you select a layout
	that the theme doesn't support then it will revert to the default layout while using that theme. Themes are also able to 
	specify additional layouts, however its important to spot them and know that while they may work with one theme they are
	unlikely to work as you expect with other themes.
	
	$context = context_system::instance();
	$PAGE->set_context($context);
	Setting the context of the page should call set_context() once with the context that is most appropriate to the page you 
	are creating. If it is a plugin then the context to use would be the context you are using for your capability checks.

    admin_externalpage_setup();
    This function call ensures the user is logged in, and makes sure that they have the proper role permission to access the 
	page.It also configures all $PAGE properties needed for navigation.
	
	$header = $SITE->fullname;
	defines the title of your custom page.
	
	$PAGE->set_title(get_string('pluginname', 'local_slack'));
	defines the title of your plugin at the browser tab.
	
	$PAGE->set_heading($header);
	to display your plugin fullname.

    echo $OUTPUT->header();
	this line prints the header of the page and adds one heading to the page at the top of the content region. Page headings 
	are very important in Moodle and should be applied consistently.
	
	echo $OUTPUT->footer();
	this line prints the footer of the page.
*/

require('../../config.php');
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/page/locallib.php');
require_once($CFG->dirroot.'/local/users/db/install.php');
$path = optional_param('path', '', PARAM_PATH); // $nameofarray = optional_param_array('nameofarray', null, PARAM_INT);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

global $CFG, $USER, $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/local/users/user_add.php');
require_login();
$context = context_system::instance();
admin_externalpage_setup('user_add', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_users'));
$PAGE->set_heading($header);
echo $OUTPUT->header();
echo "<a href='user_form.php'>Powrót do podglądu użutkowników</a>";
echo "<form action='user_add.php' method='POST'>
 <table style='margin: 2rem 0'>
 <tr><td><span>Login</span></td><td><input type='text' name='username'/></td></tr>
 <tr><td><span>Imię</span></td><td><input type='text' name='firstname'/></td></tr>
 <tr><td><span>Nazwisko</span></td><td><input type='text' name='lastname'/></td></tr>
 <tr><td><span>Email</span></td><td><input type='email' name='email'/></td></tr>
 <tr><td><span>Numer pracownika</span></td><td><input type='text' name='emp_number'/></td></tr>
 <tr><td><span>Nazwa jednostki</span></td><td><input type='dict' name='org_number'/></td></tr>
 <tr><td><span>Nazwa stanowiska</span></td><td><input type='dict' name='position'/></td></tr>
 <tr><td><input type='submit' value='dadaj'</td></tr>
 </table>
</form>";

if(isset($_POST['username']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['emp_number']) && isset($_POST['org_number'])){
    $errors = array(); // initialize the validation errors array

    // validate the required fields
    if($_POST['username'] == '') {
        $errors[] = "Username is required.";
    }
    if($_POST['firstname'] == '') {
        $errors[] = "First name is required.";
    }
    if($_POST['lastname'] == '') {
        $errors[] = "Last name is required.";
    }
    if($_POST['email'] == '') {
        $errors[] = "Email is required.";
    }
    if($_POST['org_number'] == '') {
        $errors[] = "Organizational number is required.";
    }elseif (!preg_match('/^[0-9]+$/', $_POST['org_number'])) {
        $errors[] = "Organizational number should only contain digits.";
    }
    if($_POST['position'] == '') {
        $errors[] = "Position number is required.";
    }elseif (!preg_match('/^[0-9]+$/', $_POST['position'])) {
        $errors[] = "Position ID should only contain digits.";
    }
    // validate the email field using regular expressions
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // if there are any validation errors, display them along with the submitted data
    if (count($errors) > 0) {
        echo "<p>The following errors occurred:</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $emp_number = $_POST['emp_number'];
    $org_number = $_POST['org_number'];
    $position = $_POST['position'];
    
    echo "<table class='user'>
    <thead>
    <tr>
        <th>Username</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Position</th>
        <th>Organizational Unit</th>
    </tr>
</thead>
<tbody>
        <tr>
        <td>$username</td>
        <td>$firstname</td>
        <td>$lastname</td>
        <td>$email</td>
        <td>$org_number</td>
        <td>$position</td>
        </tr>
    </table>";
    echo "<style>
    .user{
        border: .1rem solid black;
        border-collapse:collapse;
    }
    .user tr{
        border: .1rem solid black;
        border-collapse:collapse;
    }
    .user td,th{
        border: .1rem solid black;
        border-collapse:collapse;
    }
    </style>";  
    }else{
        global $DB;
        $username = $_POST['username'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $emp_number = $_POST['emp_number'];
        $org_number = $_POST['org_number'];
        $position = $_POST['position'];
        $user = new stdClass();
        $user->username = $username;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;
        $user->employee_number = $emp_number;
        $user->organizational_unit_id = $org_number;
        $user->position_id = $position;
        $user->password = "SilneHasło#6978";
        $user->city = 'London';
        $user->country = '';
        $user->lang = 'en';
        $user->timezone = '99';
        $user->maildisplay = 1;
        $user->description = 'nowy użytkownik';
        $user->descriptionformat = 1;
        $user->mailformat = 1;
        $user->maildigest = 0;
        $user->autosubscribe = 0;
        $user->trackforums = 0;
        $user->timemodified = time();
        $user_id = user_create_user($user);
        if ($user_id) {
            echo "<script>
            alert('Użytkownik dodany do bazy danych');
            </script>";
        } else {
            echo "Nie udało się dodać użytkownika do bazy danych";
        }
    }
   
    
}


echo $OUTPUT->footer();
?>


