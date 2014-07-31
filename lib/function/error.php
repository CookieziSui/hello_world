<link rel="stylesheet" type="text/css" href="../../../lib/css/error.css">
<?php
require_once __DIR__ . '/../../lib/inc/error_code.php';
/*
 * This function controls the errors display
 *  All page will require this function to handle errors
 * 
 * The error code contains three numbers:
 * The first number stand for the module:   e.g. asset  1;  task    2
 * And the following two numbers are just auto increase from 1  e.g.    101,102,103...
 */

function error($error_no) {
    ?>
    <body>
        <article>
            <div id='error' class="alert alert-error">
                <span id='error_text'>
                    [<?php echo $error_no; ?>] <?php echo $GLOBALS['ERROR_CODE'][$error_no]['en']; ?>
                </span>
            </div>
        </article>
    </body>
    <?php
}

/*
 * this functiong controls the without article error page
 */

function error_without_article($error_no) {
    ?>
    <div id='error' class="alert alert-error">
        <span id='error_text'>
            <?php echo $GLOBALS['ERROR_CODE'][$error_no]['en']; ?>
        </span>
    </div>
    <?php
}
?>
