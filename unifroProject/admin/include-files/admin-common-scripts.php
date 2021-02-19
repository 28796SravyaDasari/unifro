
<?php
if(isset($_SESSION['AlertMessage']))
{
	echo '<SCRIPT>AlertBox("'.$_SESSION['AlertMessage'].'")</SCRIPT>';
	unset($_SESSION['AlertMessage']);
}
?>
