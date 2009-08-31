<?php

include (dirname (__FILE__).'/../../../wp-config.php');

if (!current_user_can ('edit_plugins'))
	die ('<p style="color: red">You are not allowed access to this resource</p>');
	
$id = intval ($_POST['post']);
if ($id == 0)
{
	$id   = 'IS NULL';
	$name = 'sniplets';
}
else
{
	$name = $wpdb->get_row ("SELECT post_name FROM {$wpdb->posts} WHERE ID=$id");
	$name = $name->post_name;
	$id = '= '.$id;
}

header ("Content-Type: text/xml");
header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header ('Content-Disposition: attachment; filename="'.$name.'.xml"');

global $wpdb;

$snips = $wpdb->get_results ("SELECT * FROM {$wpdb->prefix}sniplets WHERE post_id $id ORDER BY position");
echo '<?xml version="1.0"?>'."\r\n";

?>
<sniplets>
<?php if (count ($snips) > 0) : ?>
	<?php foreach ($snips AS $sniplet) : ?>
		<sniplet name="<?php echo htmlspecialchars ($sniplet->name); ?>" position="<?php echo $sniplet->position ?>" template="<?php echo $sniplet->template ?>">
			<placement><?php echo $sniplet->placement; ?></placement>
			<modules><?php echo htmlspecialchars ($sniplet->modules); ?></modules>
			<content><?php echo htmlspecialchars ($sniplet->contents); ?></content>
		</sniplet>
	<?php endforeach; ?>
<?php endif; ?>
</sniplets>
