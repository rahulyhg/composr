<div data-view="PermissionsTreeEditorScreen" data-view-params="{+START,PARAMS_JSON,COLOR,USERGROUPS}{_*}{+END}">
	{TITLE}

	<p>{!PERMISSIONS_TREE_EDITOR_ABOUT_PRIVILEGE,{$PAGE_LINK*,adminzone:admin_permissions:specific}}</p>

	<div class="float-surrounder">
		<div class="pte-help-box">
			<div class="box box---permissions-tree-editor-screen"><div class="box-inner">
				<h2>{!PERMISSIONS_TREE_EDITOR_HOW_WORK}</h2>

				<p class="vertical-alignment">{!PERMISSIONS_TREE_EDITOR_ABOUT_BARRIERS,<img width="6" height="6" src="{$IMG*,led/on}" alt="" />}</p>

				<p class="vertical-alignment">{!PERMISSIONS_TREE_EDITOR_ABOUT_GROUP,<img width="29" height="17" src="{$IMG*,perm_levels/3}" alt="" />}</p>

				<p class="pte-tips">{!PERMISSIONS_TREE_EDITOR_ABOUT_MULTI_SELECT}</p>

				<p class="pte-tips">{!PERMISSIONS_TREE_EDITOR_ABOUT_HOVER_FOR_PERMISSIONS}</p>

				<p class="pte-tips">{!PERMISSIONS_TREE_EDITOR_INHERIT}</p>
			</div></div>
		</div>

		<div>
			<div class="box box---permissions-tree-editor-screen"><div class="box-inner">
				<h2>{!SITEMAP}</h2>

				<div class="pte-tree">
					<div class="pte-icon-box">
						<div class="box box---permissions-tree-editor-screen"><div class="box-inner">
							<form title="{!CHOOSE} {!USERGROUP}" action="index.php" method="post" autocomplete="off">
								<img alt="" width="19" height="11" src="{$IMG*,pte_view_help}" /><strong><label for="group">{!PERMISSIONS_TREE_EDITOR_ICON_LABEL}:</label></strong>
								<select id="group" name="group" class="js-click-update-group js-change-update-group">
									{GROUPS}
								</select>
							</form>
							<p>{!PERMISSIONS_TREE_EDITOR_ICON_SPECIFIC,<span id="group-name">{INITIAL_GROUP*}</span>}</p>
						</div></div>
					</div>

					<form title="{!PRIMARY_PAGE_FORM}" action="index.php" method="post" autocomplete="off">
						<div class="accessibility-hidden"><label for="tree_list">{!ENTRY}</label></div>
						<input class="js-change-update-perm-box" style="display: none" type="text" id="tree_list" name="tree_list" value="{$_GET*,id}" />
					</form>
					<div id="tree-list--root-tree-list" class="tree-list--root-tree-list pt-editor">
						<!-- List put in here -->
					</div>
				</div>
			</div></div>
		</div>
	</div>

	<div class="pte-set-box">
		<div class="box box---permissions-tree-editor-screen"><div class="box-inner">
			<h2>{!PERMISSIONS_TREE_EDITOR_PERMISSIONS_FOR_SELECTION}</h2>

			<p>
				{!PERMISSION_INHERITANCE_HOVER}
			</p>

			<form id="permissions-form" title="{!PRIMARY_PAGE_FORM}" method="post" action="index.php" autocomplete="off">
				{$INSERT_SPAMMER_BLACKHOLE}

				<div class="float-surrounder">
					<div style="display: none" id="selection-form-fields">
						{EDITOR}
					</div>

					<p class="right" id="selection-message">
						<em>{!PERMISSIONS_TREE_EDITOR_NONE_SELECTED}</em>
					</p>
					<div>
						<input type="button" class="button-screen-item buttons--save js-click-set-permissions" id="selection-button" disabled="disabled" value="{!SET}" />
					</div>
				</div>
			</form>
		</div></div>
	</div>
</div>
