<?php

	$CollageID = $_GET['collageid'];
	if(!is_number($CollageID)) { error(0); }

	$DB->query("SELECT Name, UserID, CategoryID FROM collages WHERE ID='$CollageID'");
	list($Name, $UserID, $CategoryID) = $DB->next_record();
	if($CategoryID == 0 && $UserID!=$LoggedUser['ID'] && !check_perms('site_collages_delete')) { error(403); }

	$DB->query("SELECT ct.GroupID,
		um.ID,
		um.Username,
		ct.Sort,
		tg.CatalogueNumber
		FROM collages_torrents AS ct
		JOIN torrents_group AS tg ON tg.ID=ct.GroupID
		LEFT JOIN users_main AS um ON um.ID=ct.UserID
		WHERE ct.CollageID='$CollageID'
		ORDER BY ct.Sort");

	$GroupIDs = $DB->collect('GroupID');

	$CollageDataList=$DB->to_array('GroupID', MYSQLI_ASSOC);
	if(count($GroupIDs)>0) {
		$TorrentList = Torrents::get_groups($GroupIDs);
		$TorrentList = $TorrentList['matches'];
	} else {
		$TorrentList = array();
	}

	View::show_header('Manage collage '.$Name);

?>

<script src="static/functions/jquery.js" type="text/javascript"></script>
<script type="text/javascript">$.noConflict();</script>
<script src="static/functions/jquery-ui.js" type="text/javascript"></script>
<script src="static/functions/jquery.tablesorter.min.js" type="text/javascript"></script>
<script src="static/functions/sort.js" type="text/javascript"></script>
<div class="thin">
	<div class="header">
		<h2>Manage collage <a href="collages.php?id=<?=$CollageID?>"><?=$Name?></a></h2>
	</div>
	<table width="100%" class="layout">
		<tr class="colhead"><td id="sorting_head">Sorting</td></tr>
		<tr>
			<td id="drag_drop_textnote">
			<ul>
				<li>Click on the headings to organize columns automatically.</li>
				<li>Sort multiple columns simultaneously by holding down the shift key and clicking other column headers.</li>
				<li>Click and drag any row to change its order.</li>
				<li>Press "Save All Changes" when you are finished sorting.</li>
				<li>Press "Edit" or "Remove" to simply modify one entry.</li>
			</ul>
			<noscript><ul><li><strong class="important_text">Note: Enable JavaScript!</strong></li></ul></noscript>
			</td>
		</tr>
	</table>

	<div class="drag_drop_save hidden">
		<input type="button" name="submit" value="Save All Changes" title="Save your changes." class="save_sortable_collage" />
	</div>
	<table id="manage_collage_table">
		<thead>
			<tr class="colhead">
				<th style="width:7%">Order</th>
				<th style="width:1%"><span><abbr title="Current Rank">#</abbr></span></th>
				<th style="width:7%"><span>Cat #</span></th>
				<th style="width:1%"><span>Year</span></th>
				<th style="width:15%"><span>Artist</span></th>
				<th><span>Torrent</span></th>
				<th style="width:1%"><span>User</span></th>
				<th style="width:1%; text-align: right" class="nobr"><span><abbr title="Modify an individual row.">Tweak</abbr></span></th>
			</tr>
		</thead>
		<tbody>
<?

	$Number = 0;
	foreach ($TorrentList as $GroupID => $Group) {
		extract(Torrents::array_group($Group));
		list(, $UserID, $Username, $Sort, $CatNum) = array_values($CollageDataList[$GroupID]);

		$Number++;

		$DisplayName = '';
		if (!empty($ExtendedArtists[1]) || !empty($ExtendedArtists[4]) || !empty($ExtendedArtists[5]) || !empty($ExtendedArtists[6])) {
			unset($ExtendedArtists[2]);
			unset($ExtendedArtists[3]);
			$DisplayName .= Artists::display_artists($ExtendedArtists, true, false);
		} elseif(count($Artists)>0) {
			$DisplayName .= Artists::display_artists(array('1'=>$Artists), true, false);
		}
		$TorrentLink = '<a href="torrents.php?id='.$GroupID.'" title="View Torrent">'.$GroupName.'</a>';
		$GroupYear = $GroupYear > 0 ? $GroupYear : '';
		if($GroupVanityHouse) { $DisplayName .= ' [<abbr title="This is a vanity house release">VH</abbr>]'; }

		$AltCSS = $Number % 2 === 0 ? 'rowa' : 'rowb';
?>
			<tr class="drag <?=$AltCSS?>" id="li_<?=$GroupID?>">
				<form class="manage_form" name="collage" action="collages.php" method="post">
					<td>
						<input class="sort_numbers" type="text" name="sort" value="<?=$Sort?>" id="sort_<?=$GroupID?>" size="4" />
					</td>
					<td><?=$Number?></td>
					<td><?=trim($CatNum) ?: '&nbsp;'?></td>
					<td><?=trim($GroupYear) ?: '&nbsp;'?></td>
					<td><?=trim($DisplayName) ?: '&nbsp;'?></td>
					<td><?=trim($TorrentLink)?></td>
					<td class="nobr"><?=Users::format_username($UserID, $Username, false, false, false)?></td>
					<td class="nobr">
						<input type="hidden" name="action" value="manage_handle" />
						<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
						<input type="hidden" name="collageid" value="<?=$CollageID?>" />
						<input type="hidden" name="groupid" value="<?=$GroupID?>" />
						<input type="submit" name="submit" value="Edit" />
						<input type="submit" name="submit" value="Remove" />
					</td>
				</form>
			</tr>
<? } ?>
		</tbody>
	</table>
	<div class="drag_drop_save hidden">
		<input type="button" name="submit" value="Save All Changes" title="Save your changes." class="save_sortable_collage" />
	</div>
	<form class="dragdrop_form hidden" name="collage" action="collages.php" method="post" id="drag_drop_collage_form">
		<div>
			<input type="hidden" name="action" value="manage_handle" />
			<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
			<input type="hidden" name="collageid" value="<?=$CollageID?>" />
			<input type="hidden" name="groupid" value="1" />
			<input type="hidden" name="drag_drop_collage_sort_order" id="drag_drop_collage_sort_order" readonly="readonly" value="" />
		</div>
	</form>
</div>
<? View::show_footer(); ?>
