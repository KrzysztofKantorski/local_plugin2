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
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/page/locallib.php');
require_once($CFG->dirroot.'/local/users/db/install.php');
require_once($CFG->dirroot.'/local/users/db/access.php');
require_once($CFG->dirroot.'/local/users/bootstrap.php');
$path = optional_param('path', '', PARAM_PATH); // $nameofarray = optional_param_array('nameofarray', null, PARAM_INT);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

global $CFG, $USER, $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/local/users/user_form.php');
require_login();
$context = context_system::instance();

admin_externalpage_setup('user_form', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_users'));
$PAGE->set_heading($header);
echo $OUTPUT->header();
echo "<a href='user_add.php'>Create a new user</a>";
global $DB;
$records_per_page = 20;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
$records = $DB->get_records('user', null, '', '*', $offset, $records_per_page);
$total_records = $DB->count_records('user');

$total_pages = ceil($total_records / $records_per_page);

?>

<table class="table table-bordered" style='margin: 2rem 0'>
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
        <?php foreach($records as $record){ ?>
            <tr>
                <td><?php echo $record->username; ?></td>
                <td><?php echo $record->firstname; ?></td>
                <td><?php echo $record->lastname; ?></td>
                <td><?php echo $record->email; ?></td>
                <td><?php echo $record->position_id; ?></td>
                <td><?php echo $record->organizational_unit_id; ?></td>
                <td><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editModal<?php echo $record->id; ?>">Edit</button></td>

<!-- Modal -->

<div class="modal fade" id="editModal<?php echo $record->id; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $record->id; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $record->id; ?>">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="user_form.php" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $record->id; ?>">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $record->username; ?>">
                    </div>
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $record->firstname; ?>">
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" class="form-control" id="lastname" name="lastname" value = "<?php echo $record->lastname; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form-control" id="email" name="email" value="<?php echo $record->email; ?>">
                    </div>
                    <div class="form-group">
                        <label for="position_id">Position id</label>
                        <input type="text" class="form-control" id="position_id" name="position_id" value="<?php echo $record->position_id; ?>">
                    </div>
                    <div class="form-group">
                        <label for="organizational_unit_id">Organization unit</label>
                        <input type="text" class="form-control" id="organizational_unit_id" name="org_number" value="<?php echo $record->organizational_unit_id;?>">
                    </div>
                    <div class="form-group">
                        <input type="submit" class="form-control" id="submit" name="submit" value="update">

        }
                    </div>
    </form>
            </tr>

           <?php
if(isset($_POST['username']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['org_number'])){
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
        $errors[] = "Organization number is required.";
    }elseif (!preg_match('/^[0-9]+$/', $_POST['org_number'])) {
        $errors[] = "Organization number should only contain digits.";
    }
    if($_POST['position_id'] == '') {
        $errors[] = "Organization number is required.";
    }elseif (!preg_match('/^[0-9]+$/', $_POST['position_id'])) {
        $errors[] = "Position ID should only contain digits.";
    }
    // validate the email field using regular expressions
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // if there are any validation errors, display them along with the submitted data
    if (count($errors) > 0) {
        echo "</table>";
        echo "<style>
        .table{
            display:none;
        }
        #text{
            display:none;
        }
        body{
            color:white;
        }
        p, ul, li, table, tr, td, th{
            color:black
        }
        table, tr, td, th{
            border: .1rem solid black
        }
        .btn-primary{
            display:none;
        }
        </style>";
        echo "</table>";
        echo "</table>";
        echo "</table>";
        echo "<p>The following errors occurred:</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";

        // set the values of the form fields to the previously submitted data
        $username = $_POST['username'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $org_number = $_POST['org_number'];
        $position = $_POST['position_id'];
        echo "<table style='border:.2rem'>
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
        </table><a href='user_form.php'>Powrót do listy użytkowników</a>"
        ;
    }
    else { // if there are no validation errors, update the user record in the database
        global $DB;
        $username = $_POST['username'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $org_number = $_POST['org_number'];
        $position = $_POST['position_id'];
        $user_id = $DB->get_field('user', 'id', array('username' => $username));
        $user = new stdClass();
        $user->username = $username;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;
        $user->id = $user_id;
        $user->organizational_unit_id = $org_number;
        $user->position_id = $position;
        $user_id = user_update_user($user);
    }
}
           ?>
        <?php } ?>
    </tbody>
</table>

<ul class="pagination">
    <?php if($page > 1){ ?>
        <li><a href="?page=<?php echo ($page - 1); ?>">Previous</a></li>
    <?php } ?>
    <?php for($i = 1; $i <= $total_pages; $i++){ ?>
        <li <?php if($i == $page){ echo 'class="active"'; } ?>>
            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
    <?php } ?>
    <?php if($page < $total_pages){ ?>
        <li><a href="?page=<?php echo ($page + 1); ?>">Next</a></li>
    <?php } ?>
</ul>
<?php
echo $OUTPUT->footer();
?>


