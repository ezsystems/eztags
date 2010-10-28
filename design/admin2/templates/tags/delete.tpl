<div class="context-block tags-delete">
	<div class="box-header">
		<h1 class="context-title">{"Delete tag"|i18n('extension/eztags/tags/edit')}: {$keyword} [{$id}]</h1>
		<div class="header-mainline"></div>
	</div>

	<div class="box-content">
		<p>{'Are you sure you want to delete the "%keyword" tag? All children tags will also be deleted and removed from existing objects.'|i18n( 'extension/eztags/tags/edit',, hash('%keyword', $keyword))}</p>

		<form name="tagdeleteform" id="tagdeleteform" enctype="multipart/form-data" method="post" action={concat('tags/delete/', $id)|ezurl}>
			<div class="controlbar">
				<div class="block">
					<input class="defaultbutton" type="submit" name="YesButton" value="{'Yes'|i18n( 'extension/eztags/tags/edit' )}" />
					<input class="button" type="submit" name="NoButton" value="{'No'|i18n( 'extension/eztags/tags/edit' )}" />
				</div>
			</div>
		</form>
	</div>
</div>