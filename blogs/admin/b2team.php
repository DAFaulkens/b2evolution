<?php
require_once (dirname(__FILE__).'/_header.php');
$title = T_('User management');

param( 'action', 'string' );

switch ($action) {
	
case "promote":
	param( 'prom', 'string' );
	param( 'id', 'integer' );
	
	if (empty($prom))
	{
		header("Location: b2team.php");
	}

	$user_data=get_userdata($id);
	$usertopromote_level=$user_data[13];

	if ($user_level <= $usertopromote_level) {
		die(T_('Can\'t change the level of an user whose level is higher than yours.'));
	}

	if ($prom == "up") {
		$sql="UPDATE $tableusers SET user_level=user_level+1 WHERE ID = $id";
	} elseif ($prom == "down") {
		$sql="UPDATE $tableusers SET user_level=user_level-1 WHERE ID = $id";
	}
	$result=mysql_query($sql) or die("Couldn't change $id's level.");

	header("Location: b2team.php");

break;

case "delete":
	param( 'id', 'integer' );

	if (!$id) {
		header("Location: b2team.php");
	}

	$user_data=get_userdata($id);
	$usertodelete_level=$user_data[13];

	if ($user_level <= $usertodelete_level)
	die(T_('Can\'t delete an user whose level is higher than yours.'));

	$sql="DELETE FROM $tableusers WHERE ID = $id";
	$result=mysql_query($sql) or die(sprintf( T_('Couldn\'t delete user #%d.'), $id ));

	// TODO: MORE DB STUFF:
	$sql="DELETE FROM $tableposts WHERE post_author = $id";
	$result=mysql_query($sql) or die( sprintf( T_('Couldn\'t delete user #%d\'s posts.'), $id ) );

	header("Location: b2team.php");

break;

default:
	require( dirname(__FILE__).'/_menutop.php');
	require( dirname(__FILE__).'/_menutop_end.php');
	?>
	<div class="panelblock">
	<table cellspacing="0" cellpadding="5" border="0" width="100%">
	<tr>
	<td><?php echo T_('Click on an user\'s login name to see his/her complete Profile.') ?><br />
	<?php echo T_('To edit your Profile, click on your login name.') ?></td>
	</tr>
</table>
</div>

<div class="panelblock">
	<p><strong>Active users</strong>
	<table cellpadding="5" cellspacing="0">
	<tr>
	<td class="tabletoprow"><?php echo T_('ID') ?></td>
	<td class="tabletoprow"><?php echo T_('Nickname') ?></td>
	<td class="tabletoprow"><?php echo T_('Name') ?></td>
	<td class="tabletoprow"><?php echo T_('Email') ?></td>
	<td class="tabletoprow"><?php echo T_('URL') ?></td>
	<td class="tabletoprow"><?php echo T_('Level') ?></td>
	<?php if ($user_level > 3) { ?>
	<td class="tabletoprow"><?php /* TRANS: table header for user list */ echo T_('Login ') ?></td>
	<?php } ?>
	</tr>
	<?php
	$request = " SELECT * FROM $tableusers WHERE user_level>0 ORDER BY ID";
	$result = mysql_query($request);
	while($row = mysql_fetch_object($result)) {
		$user_data = get_userdata2($row->ID);
		echo "<tr>\n<!--".$user_data["user_login"]."-->\n";
		$email = $user_data["user_email"];
		$url = $user_data["user_url"];
		$bg1 = ($user_data["user_login"] == $user_login) ? "style=\"background-image: url('img/b2button.gif');\"" : "bgcolor=\"#dddddd\"";
		$bg2 = ($user_data["user_login"] == $user_login) ? "style=\"background-image: url('img/b2button.gif');\"" : "bgcolor=\"#eeeeee\"";
		echo "<td $bg1>".$user_data["ID"]."</td>\n";
		echo "<td $bg2><strong><a href=\"javascript:profile(".$user_data["ID"].")\">".$user_data["user_nickname"]."</a></strong></td>\n";
		echo "<td $bg1>".$user_data["user_firstname"]."&nbsp;".$user_data["user_lastname"]."</td>\n";
		echo "<td $bg2>&nbsp;<a href=\"mailto:$email\" title=\"e-mail: $email\"><img src=\"img/email.gif\" border=\"0\" alt=\"e-mail: $email\" /></a>&nbsp;</td>";
		echo "<td $bg1>&nbsp;";
		if (($user_data["user_url"] != "http://") and ($user_data["user_url"] != ""))
			echo "<a href=\"$url\" target=\"_blank\" title=\"website: $url\"><img src=\"img/url.gif\" border=\"0\" alt=\"website: $url\" /></a>&nbsp;";
		echo "</td>\n";
		echo "<td $bg2>".$user_data["user_level"];
		if (($user_level >= 2) and ($user_level > ($user_data["user_level"] + 1)))
			echo " <a href=\"b2team.php?action=promote&id=".$user_data["ID"]."&prom=up\">+</a> ";
		if (($user_level >= 2) and ($user_level > $user_data["user_level"]) and ($user_data["user_level"] > 0))
			echo " <a href=\"b2team.php?action=promote&id=".$user_data["ID"]."&prom=down\">-</a> ";
		echo "</td>\n";
		if ($user_level > 3) {
			echo "<td $bg1>".$user_data["user_login"]."</td>\n";
		}
		echo "</tr>\n";
	}
	
	?>
	
	</table>
	</p>
</div>
<?php
	$request = " SELECT * FROM $tableusers WHERE user_level=0 ORDER BY ID";
	$result = mysql_query($request);
	if (mysql_num_rows($result)) {
?>
<div class="panelblock">
	<p><strong>Inactive users (level 0)</strong>
	<table cellpadding="5" cellspacing="0">
	<tr>
	<td class="tabletoprow"><?php echo T_('ID') ?></td>
	<td class="tabletoprow"><?php echo T_('Nickname') ?></td>
	<td class="tabletoprow"><?php echo T_('Name') ?></td>
	<td class="tabletoprow"><?php echo T_('Email') ?></td>
	<td class="tabletoprow"><?php echo T_('URL') ?></td>
	<td class="tabletoprow"><?php echo T_('Level') ?></td>
	<?php if ($user_level > 3) { ?>
	<td class="tabletoprow"><?php /* TRANS: table header for user list */ echo T_('Login ') ?></td>
	<?php } ?>
	</tr>
	<?php
	while($row = mysql_fetch_object($result)) 
	{
		$user_data = get_userdata2($row->ID);
		echo "<tr>\n<!--".$user_data["user_login"]."-->\n";
		$email = $user_data["user_email"];
		$url = $user_data["user_url"];
		$bg1 = ($user_data["user_login"] == $user_login) ? "style=\"background-image: url('img/b2button.gif');\"" : "bgcolor=\"#dddddd\"";
		$bg2 = ($user_data["user_login"] == $user_login) ? "style=\"background-image: url('img/b2button.gif');\"" : "bgcolor=\"#eeeeee\"";
		echo "<td $bg1>".$user_data["ID"]."</td>\n";
		echo "<td $bg2><strong><a href=\"javascript:profile(".$user_data["ID"].")\">".$user_data["user_nickname"]."</a></strong></td>\n";
		echo "<td $bg1>".$user_data["user_firstname"]."&nbsp;".$user_data["user_lastname"]."</td>\n";
		echo "<td $bg1>&nbsp;<a href=\"mailto:".antispambot($email)."\" title=\"e-mail: ".antispambot($email)."\"><img src=\"img/email.gif\" border=\"0\" alt=\"e-mail: ".antispambot($email)."\" /></a>&nbsp;</td>";
		echo "<td $bg2>&nbsp;";
		if (($user_data["user_url"] != "http://") and ($user_data["user_url"] != ""))
			echo "<a href=\"$url\" target=\"_blank\" title=\"website: $url\"><img src=\"img/url.gif\" border=\"0\" alt=\"website: $url\" /></a>&nbsp;";
		echo "</td>\n";
		echo "<td $bg1>".$user_data["user_level"];
		if ($user_level >= 2)
			echo " <a href=\"b2team.php?action=promote&id=".$user_data["ID"]."&prom=up\">+</a> ";
		if ($user_level >= 3)
			echo " <a href=\"b2team.php?action=delete&id=".$user_data["ID"]."\" style=\"color:red;font-weight:bold;\">X</a> ";
		echo "</td>\n";
		if ($user_level > 3) {
			echo "<td $bg2>".$user_data["user_login"]."</td>\n";
		}
		echo "</tr>\n";
	}
	
	?>
	
	</table>
	</p>
</div>

	<?php 
	}
	if ($user_level >= 3) { ?>

<div class="panelblock">
	<?php echo T_('To delete an user, bring his/her level to zero, then click on the red cross.') ?><br />
	<strong><?php echo T_('Warning') ?>:</strong> <?php echo T_('deleting an user also deletes all posts made by this user.') ?>
</div>
	<?php
}

break;
}
	
/* </Team> */
require( dirname(__FILE__).'/_footer.php' ); 
?>