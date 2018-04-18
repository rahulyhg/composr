DROP TABLE IF EXISTS cms_alternative_ids;

CREATE TABLE cms_alternative_ids (
    resource_type varchar(80) NOT NULL,
    resource_id varchar(80) NOT NULL,
    resource_moniker varchar(80) NOT NULL,
    resource_label varchar(255) NOT NULL,
    resource_guid varchar(80) NOT NULL,
    resource_resource_fs_hook varchar(80) NOT NULL,
    PRIMARY KEY (resource_type, resource_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_alternative_ids ADD INDEX resource_guid (resource_guid);

ALTER TABLE cms10_alternative_ids ADD INDEX resource_label (resource_label(250));

ALTER TABLE cms10_alternative_ids ADD INDEX resource_moniker (resource_moniker,resource_type);

ALTER TABLE cms10_alternative_ids ADD INDEX resource_moniker_uniq (resource_moniker,resource_resource_fs_hook);

DROP TABLE IF EXISTS cms_attachment_refs;

CREATE TABLE cms_attachment_refs (
    id integer unsigned auto_increment NOT NULL,
    r_referer_type varchar(80) NOT NULL,
    r_referer_id varchar(80) NOT NULL,
    a_id integer NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_attachments;

CREATE TABLE cms_attachments (
    id integer unsigned auto_increment NOT NULL,
    a_member_id integer NOT NULL,
    a_file_size integer NULL,
    a_url varchar(255) NOT NULL,
    a_description varchar(255) NOT NULL,
    a_thumb_url varchar(255) NOT NULL,
    a_original_filename varchar(255) NOT NULL,
    a_num_downloads integer NOT NULL,
    a_last_downloaded_time integer NULL,
    a_add_time integer NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_attachments ADD INDEX attachmentlimitcheck (a_add_time);

ALTER TABLE cms10_attachments ADD INDEX ownedattachments (a_member_id);

DROP TABLE IF EXISTS cms_authors;

CREATE TABLE cms_authors (
    author varchar(80) NOT NULL,
    url varchar(255) BINARY NOT NULL,
    member_id integer NULL,
    description longtext NOT NULL,
    skills longtext NOT NULL,
    description__text_parsed longtext NOT NULL,
    description__source_user integer DEFAULT 1 NOT NULL,
    skills__text_parsed longtext NOT NULL,
    skills__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (author)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_authors ADD FULLTEXT description (description);

ALTER TABLE cms10_authors ADD FULLTEXT skills (skills);

ALTER TABLE cms10_authors ADD INDEX findmemberlink (member_id);

DROP TABLE IF EXISTS cms_autosave;

CREATE TABLE cms_autosave (
    id integer unsigned auto_increment NOT NULL,
    a_member_id integer NOT NULL,
    a_key longtext NOT NULL,
    a_value longtext NOT NULL,
    a_time integer unsigned NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_autosave ADD INDEX myautosaves (a_member_id);

DROP TABLE IF EXISTS cms_blocks;

CREATE TABLE cms_blocks (
    block_name varchar(80) NOT NULL,
    block_author varchar(80) NOT NULL,
    block_organisation varchar(80) NOT NULL,
    block_hacked_by varchar(80) NOT NULL,
    block_hack_version integer NULL,
    block_version integer NOT NULL,
    PRIMARY KEY (block_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_cache;

CREATE TABLE cms_cache (
    id integer unsigned auto_increment NOT NULL,
    cached_for varchar(80) NOT NULL,
    identifier varchar(40) NOT NULL,
    the_theme varchar(40) NOT NULL,
    staff_status tinyint(1) NULL,
    the_member integer NULL,
    groups varchar(255) NOT NULL,
    is_bot tinyint(1) NULL,
    timezone varchar(40) NOT NULL,
    lang varchar(5) NOT NULL,
    the_value longtext NOT NULL,
    dependencies longtext NOT NULL,
    date_and_time integer unsigned NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_cache ADD INDEX cached_ford (date_and_time);

ALTER TABLE cms10_cache ADD INDEX cached_fore (cached_for);

ALTER TABLE cms10_cache ADD INDEX cached_forf (cached_for,identifier,the_theme,lang,staff_status,the_member,is_bot);

ALTER TABLE cms10_cache ADD INDEX cached_forh (the_theme);

DROP TABLE IF EXISTS cms_cache_on;

CREATE TABLE cms_cache_on (
    cached_for varchar(80) NOT NULL,
    cache_on longtext NOT NULL,
    special_cache_flags integer NOT NULL,
    cache_ttl integer NOT NULL,
    PRIMARY KEY (cached_for)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_cached_comcode_pages;

CREATE TABLE cms_cached_comcode_pages (
    the_zone varchar(80) NOT NULL,
    the_page varchar(80) NOT NULL,
    string_index longtext NOT NULL,
    the_theme varchar(80) NOT NULL,
    cc_page_title longtext NOT NULL,
    string_index__text_parsed longtext NOT NULL,
    string_index__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (the_zone, the_page, the_theme)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_cached_comcode_pages ADD FULLTEXT cc_page_title (cc_page_title);

ALTER TABLE cms10_cached_comcode_pages ADD FULLTEXT page_search__combined (cc_page_title,string_index);

ALTER TABLE cms10_cached_comcode_pages ADD FULLTEXT string_index (string_index);

ALTER TABLE cms10_cached_comcode_pages ADD INDEX ccp_join (the_page,the_zone);

ALTER TABLE cms10_cached_comcode_pages ADD INDEX ftjoin_ccpt (cc_page_title(250));

ALTER TABLE cms10_cached_comcode_pages ADD INDEX ftjoin_ccsi (string_index(250));

DROP TABLE IF EXISTS cms_captchas;

CREATE TABLE cms_captchas (
    si_session_id varchar(80) NOT NULL,
    si_time integer unsigned NOT NULL,
    si_code varchar(80) NOT NULL,
    PRIMARY KEY (si_session_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_captchas ADD INDEX si_time (si_time);

DROP TABLE IF EXISTS cms_comcode_pages;

CREATE TABLE cms_comcode_pages (
    the_zone varchar(80) NOT NULL,
    the_page varchar(80) NOT NULL,
    p_parent_page varchar(80) NOT NULL,
    p_validated tinyint(1) NOT NULL,
    p_edit_date integer unsigned NULL,
    p_add_date integer unsigned NOT NULL,
    p_submitter integer NOT NULL,
    p_show_as_edit tinyint(1) NOT NULL,
    p_order integer NOT NULL,
    PRIMARY KEY (the_zone, the_page)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_comcode_pages (the_zone, the_page, p_parent_page, p_validated, p_edit_date, p_add_date, p_submitter, p_show_as_edit, p_order) VALUES ('site', 'userguide_comcode', 'help', 1, NULL, 1524093347, 2, 0, 0);
INSERT INTO cms_comcode_pages (the_zone, the_page, p_parent_page, p_validated, p_edit_date, p_add_date, p_submitter, p_show_as_edit, p_order) VALUES ('', 'keymap', 'help', 1, NULL, 1524093347, 2, 0, 0);

ALTER TABLE cms10_comcode_pages ADD INDEX p_add_date (p_add_date);

ALTER TABLE cms10_comcode_pages ADD INDEX p_order (p_order);

ALTER TABLE cms10_comcode_pages ADD INDEX p_submitter (p_submitter);

ALTER TABLE cms10_comcode_pages ADD INDEX p_validated (p_validated);

DROP TABLE IF EXISTS cms_config;

CREATE TABLE cms_config (
    c_name varchar(80) NOT NULL,
    c_set tinyint(1) NOT NULL,
    c_value longtext NOT NULL,
    c_value_trans longtext NOT NULL,
    c_needs_dereference tinyint(1) NOT NULL,
    PRIMARY KEY (c_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_config ADD FULLTEXT c_value_trans (c_value_trans);

DROP TABLE IF EXISTS cms_content_privacy;

CREATE TABLE cms_content_privacy (
    content_type varchar(80) NOT NULL,
    content_id varchar(80) NOT NULL,
    guest_view tinyint(1) NOT NULL,
    member_view tinyint(1) NOT NULL,
    friend_view tinyint(1) NOT NULL,
    PRIMARY KEY (content_type, content_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_content_privacy ADD INDEX friend_view (friend_view);

ALTER TABLE cms10_content_privacy ADD INDEX guest_view (guest_view);

ALTER TABLE cms10_content_privacy ADD INDEX member_view (member_view);

DROP TABLE IF EXISTS cms_content_privacy__members;

CREATE TABLE cms_content_privacy__members (
    content_type varchar(80) NOT NULL,
    content_id varchar(80) NOT NULL,
    member_id integer NOT NULL,
    PRIMARY KEY (content_type, content_id, member_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_content_regions;

CREATE TABLE cms_content_regions (
    content_type varchar(80) NOT NULL,
    content_id varchar(80) NOT NULL,
    region varchar(80) NOT NULL,
    PRIMARY KEY (content_type, content_id, region)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_cron_caching_requests;

CREATE TABLE cms_cron_caching_requests (
    id integer unsigned auto_increment NOT NULL,
    c_codename varchar(80) NOT NULL,
    c_map longtext NOT NULL,
    c_lang varchar(5) NOT NULL,
    c_theme varchar(80) NOT NULL,
    c_staff_status tinyint(1) NULL,
    c_member integer NULL,
    c_groups varchar(255) NOT NULL,
    c_is_bot tinyint(1) NULL,
    c_timezone varchar(40) NOT NULL,
    c_store_as_tempcode tinyint(1) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_cron_caching_requests ADD INDEX c_compound (c_codename,c_theme,c_lang,c_timezone);

ALTER TABLE cms10_cron_caching_requests ADD INDEX c_is_bot (c_is_bot);

ALTER TABLE cms10_cron_caching_requests ADD INDEX c_store_as_tempcode (c_store_as_tempcode);

DROP TABLE IF EXISTS cms_db_meta;

CREATE TABLE cms_db_meta (
    m_table varchar(80) NOT NULL,
    m_name varchar(80) NOT NULL,
    m_type varchar(80) NOT NULL,
    PRIMARY KEY (m_table, m_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('translate', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('translate', 'language', '*LANGUAGE_NAME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('translate', 'importance_level', 'SHORT_INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('translate', 'text_original', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('translate', 'text_parsed', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('translate', 'broken', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('translate', 'source_user', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('values', 'the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('values', 'the_value', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('values', 'date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('config', 'c_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('config', 'c_set', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('config', 'c_value', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('config', 'c_value_trans', '?LONG_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('config', 'c_needs_dereference', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_privileges', 'group_id', '*INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_privileges', 'privilege', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_privileges', 'the_page', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_privileges', 'module_the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_privileges', 'category_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_privileges', 'the_value', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('privilege_list', 'p_section', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('privilege_list', 'the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('privilege_list', 'the_default', '*BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_file_size', '?INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_url', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_description', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_thumb_url', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_original_filename', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_num_downloads', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_last_downloaded_time', '?INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachments', 'a_add_time', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachment_refs', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachment_refs', 'r_referer_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachment_refs', 'r_referer_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('attachment_refs', 'a_id', 'AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('zones', 'zone_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('zones', 'zone_title', 'SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('zones', 'zone_default_page', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('zones', 'zone_header_text', 'SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('zones', 'zone_theme', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('zones', 'zone_require_session', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('modules', 'module_the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('modules', 'module_author', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('modules', 'module_organisation', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('modules', 'module_hacked_by', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('modules', 'module_hack_version', '?INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('modules', 'module_version', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('blocks', 'block_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('blocks', 'block_author', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('blocks', 'block_organisation', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('blocks', 'block_hacked_by', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('blocks', 'block_hack_version', '?INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('blocks', 'block_version', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'the_session', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'last_activity', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'ip', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'session_confirmed', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'session_invisible', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'cache_username', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'the_zone', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'the_page', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'the_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'the_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sessions', 'the_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('https_pages', 'https_page_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_category_access', 'module_the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_category_access', 'category_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_category_access', 'group_id', '*GROUP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('seo_meta', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('seo_meta', 'meta_for_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('seo_meta', 'meta_for_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('seo_meta', 'meta_description', 'LONG_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('seo_meta_keywords', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('seo_meta_keywords', 'meta_for_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('seo_meta_keywords', 'meta_for_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('seo_meta_keywords', 'meta_keyword', 'SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_join_log', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_join_log', 'member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_join_log', 'usergroup_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_join_log', 'join_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_password_history', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_password_history', 'p_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_password_history', 'p_hash_salted', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_password_history', 'p_salt', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_password_history', 'p_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_cpf_perms', 'member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_cpf_perms', 'field_id', '*AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_cpf_perms', 'guest_view', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_cpf_perms', 'member_view', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_cpf_perms', 'friend_view', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_cpf_perms', 'group_view', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_emoticons', 'e_code', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_emoticons', 'e_theme_img_code', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_emoticons', 'e_relevance_level', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_emoticons', 'e_use_topics', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_emoticons', 'e_is_special', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_locked', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_name', 'SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_description', 'LONG_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_default', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_public_view', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_owner_view', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_owner_set', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_required', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_show_in_posts', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_show_in_post_previews', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_order', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_only_group', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_encrypted', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_show_on_join_form', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_custom_fields', 'cf_options', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'mf_member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_1', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_2', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_3', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_4', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_5', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_6', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_7', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_8', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_invites', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_invites', 'i_inviter', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_invites', 'i_email_address', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_invites', 'i_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_invites', 'i_taken', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_members', 'gm_group_id', '*GROUP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_members', 'gm_member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_members', 'gm_validated', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_username', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_pass_hash_salted', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_pass_salt', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_theme', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_avatar_url', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_validated', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_validated_email_confirm_code', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_cache_num_posts', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_cache_warnings', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_join_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_timezone_offset', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_primary_group', 'GROUP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_last_visit_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_last_submit_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_signature', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_is_perm_banned', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_preview_posts', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_dob_day', '?SHORT_INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_dob_month', '?SHORT_INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_dob_year', '?INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_reveal_age', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_email_address', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_photo_url', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_photo_thumb_url', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_views_signatures', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_auto_monitor_contrib_content', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_language', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_ip_address', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_allow_emails', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_allow_emails_from_staff', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_highlighted_name', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_pt_allow', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_pt_rules_text', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_max_email_attach_size_mb', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_password_change_code', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_password_compat_scheme', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_on_probation_until', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_profile_views', 'UINTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_total_sessions', 'UINTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_members', 'm_auto_mark_read', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_name', 'SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_is_default', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_is_presented_at_install', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_is_super_admin', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_is_super_moderator', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_group_leader', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_title', 'SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_promotion_target', '?GROUP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_promotion_threshold', '?INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_flood_control_submit_secs', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_flood_control_access_secs', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_gift_points_base', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_gift_points_per_day', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_max_daily_upload_mb', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_max_attachments_per_post', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_max_avatar_width', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_max_avatar_height', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_max_post_length_comcode', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_max_sig_length_comcode', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_enquire_on_new_ips', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_rank_image', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_hidden', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_order', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_rank_image_pri_only', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_open_membership', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_groups', 'g_is_private_club', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('match_key_messages', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('match_key_messages', 'k_message', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('match_key_messages', 'k_match_key', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_zone_access', 'zone_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_zone_access', 'group_id', '*GROUP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_page_access', 'page_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_page_access', 'zone_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('group_page_access', 'group_id', '*GROUP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forum_groupings', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forum_groupings', 'c_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forum_groupings', 'c_description', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forum_groupings', 'c_expanded_by_default', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_name', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_description', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_forum_grouping_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_parent_forum', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_position', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_order_sub_alpha', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_post_count_increment', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_intro_question', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_intro_answer', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_cache_num_topics', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_cache_num_posts', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_cache_last_topic_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_cache_last_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_cache_last_time', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_cache_last_username', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_cache_last_member_id', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_cache_last_forum_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_redirection', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_order', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_is_threaded', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forums', 'f_allows_anonymous_posts', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_pinned', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_sunk', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cascading', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_forum_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_pt_from', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_pt_to', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_pt_from_category', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_pt_to_category', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_description', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_description_link', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_emoticon', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_num_views', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_validated', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_is_open', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_poll_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_first_post_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_first_time', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_first_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_first_post', '?LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_first_username', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_first_member_id', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_last_post_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_last_time', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_last_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_last_username', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_last_member_id', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_topics', 't_cache_num_posts', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_post', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_ip_address', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_poster', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_intended_solely_for', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_poster_name_if_guest', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_validated', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_topic_id', 'AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_cache_forum_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_last_edit_time', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_last_edit_by', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_is_emphasised', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_skip_sig', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_posts', 'p_parent_id', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_special_pt_access', 's_member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_special_pt_access', 's_topic_id', '*AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_saved_warnings', 's_title', '*SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_saved_warnings', 's_explanation', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_saved_warnings', 's_message', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forum_intro_ip', 'i_forum_id', '*AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forum_intro_ip', 'i_ip', '*IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forum_intro_member', 'i_forum_id', '*AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_forum_intro_member', 'i_member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_post_templates', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_post_templates', 't_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_post_templates', 't_text', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_post_templates', 't_forum_multi_code', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_post_templates', 't_use_default_forums', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_polls', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_polls', 'po_question', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_polls', 'po_cache_total_votes', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_polls', 'po_is_private', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_polls', 'po_is_open', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_polls', 'po_minimum_selections', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_polls', 'po_maximum_selections', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_polls', 'po_requires_reply', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_answers', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_answers', 'pa_poll_id', 'AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_answers', 'pa_answer', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_answers', 'pa_cache_num_votes', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_votes', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_votes', 'pv_poll_id', 'AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_votes', 'pv_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_votes', 'pv_answer_id', 'AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_poll_votes', 'pv_ip', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'mm_name', 'SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'mm_post_text', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'mm_move_to', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'mm_pin_state', '?BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'mm_sink_state', '?BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'mm_open_state', '?BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'mm_forum_multi_code', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_multi_moderations', 'mm_title_suffix', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'w_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'w_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'w_explanation', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'w_by', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'w_is_warning', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'p_silence_from_topic', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'p_silence_from_forum', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'p_probation', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'p_banned_ip', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'p_charged_points', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'p_banned_member', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_warnings', 'p_changed_usergroup_from', '?GROUP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_moderator_logs', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_moderator_logs', 'l_the_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_moderator_logs', 'l_param_a', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_moderator_logs', 'l_param_b', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_moderator_logs', 'l_date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_moderator_logs', 'l_reason', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_moderator_logs', 'l_by', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_known_login_ips', 'i_member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_known_login_ips', 'i_ip', '*IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_known_login_ips', 'i_val_code', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_read_logs', 'l_member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_read_logs', 'l_topic_id', '*AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_read_logs', 'l_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_menu', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_order', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_parent', '?AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_caption', 'SHORT_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_caption_long', 'SHORT_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_url', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_check_permissions', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_expanded', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_new_window', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_include_sitemap', 'SHORT_INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_page_only', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('menu_items', 'i_theme_img_code', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'trackback_for_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'trackback_for_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'trackback_ip', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'trackback_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'trackback_url', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'trackback_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'trackback_excerpt', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('trackbacks', 'trackback_name', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('captchas', 'si_session_id', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('captchas', 'si_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('captchas', 'si_code', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_tracking', 'mt_member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_tracking', 'mt_cache_username', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_tracking', 'mt_time', '*TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_tracking', 'mt_page', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_tracking', 'mt_type', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_tracking', 'mt_id', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache_on', 'cached_for', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache_on', 'cache_on', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache_on', 'special_cache_flags', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache_on', 'cache_ttl', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('webstandards_checked_once', 'hash', '*SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('edit_pings', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('edit_pings', 'the_page', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('edit_pings', 'the_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('edit_pings', 'the_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('edit_pings', 'the_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('edit_pings', 'the_member', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('values_elective', 'the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('values_elective', 'the_value', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('values_elective', 'date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('tutorial_links', 'the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('tutorial_links', 'the_value', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_privileges', 'member_id', '*INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_privileges', 'privilege', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_privileges', 'the_page', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_privileges', 'module_the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_privileges', 'category_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_privileges', 'the_value', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_privileges', 'active_until', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_zone_access', 'zone_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_zone_access', 'member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_zone_access', 'active_until', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_page_access', 'page_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_page_access', 'zone_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_page_access', 'member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_page_access', 'active_until', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_category_access', 'module_the_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_category_access', 'category_name', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_category_access', 'member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('member_category_access', 'active_until', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('autosave', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('autosave', 'a_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('autosave', 'a_key', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('autosave', 'a_value', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('autosave', 'a_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('messages_to_render', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('messages_to_render', 'r_session_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('messages_to_render', 'r_message', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('messages_to_render', 'r_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('messages_to_render', 'r_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_url', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_meta_title', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_keywords', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_description', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_image_url', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_mime_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_json_discovery', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_title_cache', 't_xml_discovery', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('rating', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('rating', 'rating_for_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('rating', 'rating_for_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('rating', 'rating_member', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('rating', 'rating_ip', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('rating', 'rating_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('rating', 'rating', 'SHORT_INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'the_zone', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'the_page', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'p_parent_page', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'p_validated', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'p_edit_date', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'p_add_date', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'p_submitter', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'p_show_as_edit', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('comcode_pages', 'p_order', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cached_comcode_pages', 'the_zone', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cached_comcode_pages', 'the_page', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cached_comcode_pages', 'string_index', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cached_comcode_pages', 'the_theme', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cached_comcode_pages', 'cc_page_title', '?SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_id_monikers', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_id_monikers', 'm_resource_page', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_id_monikers', 'm_resource_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_id_monikers', 'm_resource_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_id_monikers', 'm_moniker', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_id_monikers', 'm_moniker_reversed', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_id_monikers', 'm_deprecated', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('url_id_monikers', 'm_manually_chosen', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('review_supplement', 'r_post_id', '*AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('review_supplement', 'r_rating_type', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('review_supplement', 'r_rating', 'SHORT_INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('review_supplement', 'r_topic_id', 'AUTO_LINK');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('review_supplement', 'r_rating_for_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('review_supplement', 'r_rating_for_type', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_subject', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_message', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_to_email', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_extra_cc_addresses', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_extra_bcc_addresses', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_join_time', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_to_name', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_from_email', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_from_name', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_priority', 'SHORT_INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_attachments', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_no_cc', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_as', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_as_admin', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_in_html', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_url', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_queued', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('logged_mail_messages', 'm_template', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('link_tracker', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('link_tracker', 'c_date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('link_tracker', 'c_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('link_tracker', 'c_ip_address', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('link_tracker', 'c_url', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('incoming_uploads', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('incoming_uploads', 'i_submitter', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('incoming_uploads', 'i_date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('incoming_uploads', 'i_orig_filename', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('incoming_uploads', 'i_save_url', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'cached_for', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'identifier', 'MINIID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'the_theme', 'MINIID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'staff_status', '?BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'the_member', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'groups', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'is_bot', '?BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'timezone', 'MINIID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'lang', 'LANGUAGE_NAME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'the_value', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'dependencies', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cache', 'date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_member_timeouts', 'member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_member_timeouts', 'group_id', '*GROUP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_group_member_timeouts', 'timeout', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('temp_block_permissions', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('temp_block_permissions', 'p_session_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('temp_block_permissions', 'p_block_constraints', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('temp_block_permissions', 'p_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_codename', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_map', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_lang', 'LANGUAGE_NAME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_theme', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_staff_status', '?BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_member', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_groups', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_is_bot', '?BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_timezone', 'MINIID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('cron_caching_requests', 'c_store_as_tempcode', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('notifications_enabled', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('notifications_enabled', 'l_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('notifications_enabled', 'l_notification_code', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('notifications_enabled', 'l_code_category', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('notifications_enabled', 'l_setting', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_subject', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_message', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_from_member_id', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_to_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_priority', 'SHORT_INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_no_cc', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_notification_code', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_code_category', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_frequency', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_tin', 'd_read', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_consumed', 'c_member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_consumed', 'c_frequency', '*INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('digestives_consumed', 'c_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('alternative_ids', 'resource_type', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('alternative_ids', 'resource_id', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('alternative_ids', 'resource_moniker', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('alternative_ids', 'resource_label', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('alternative_ids', 'resource_guid', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('alternative_ids', 'resource_resource_fs_hook', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('f_member_custom_fields', 'field_9', '?INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('email_bounces', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('email_bounces', 'b_email_address', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('email_bounces', 'b_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('email_bounces', 'b_subject', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('email_bounces', 'b_body', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_privacy', 'content_type', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_privacy', 'content_id', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_privacy', 'guest_view', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_privacy', 'member_view', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_privacy', 'friend_view', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_privacy__members', 'content_type', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_privacy__members', 'content_id', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_privacy__members', 'member_id', '*MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('task_queue', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('task_queue', 't_title', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('task_queue', 't_hook', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('task_queue', 't_args', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('task_queue', 't_member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('task_queue', 't_secure_ref', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('task_queue', 't_send_notification', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('task_queue', 't_locked', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'page_link', '*SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'set_number', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'add_date', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'edit_date', '?TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'last_updated', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'is_deleted', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'priority', 'REAL');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'refreshfreq', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('sitemap_cache', 'guest_access', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('urls_checked', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('urls_checked', 'url', 'LONG_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('urls_checked', 'url_exists', 'BINARY');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('urls_checked', 'url_check_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_regions', 'content_type', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_regions', 'content_id', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('content_regions', 'region', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('unbannable_ip', 'ip', '*IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('unbannable_ip', 'note', 'SHORT_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('post_tokens', 'token', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('post_tokens', 'generation_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('post_tokens', 'member_id', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('post_tokens', 'session_id', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('post_tokens', 'ip_address', 'IP');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('post_tokens', 'usage_tally', 'INTEGER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('authors', 'author', '*ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('authors', 'url', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('authors', 'member_id', '?MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('authors', 'description', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('authors', 'skills', 'LONG_TRANS__COMCODE');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('filedump', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('filedump', 'name', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('filedump', 'path', 'URLPATH');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('filedump', 'description', 'SHORT_TRANS');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('filedump', 'the_member', 'MEMBER');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('failedlogins', 'id', '*AUTO');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('failedlogins', 'failed_account', 'ID_TEXT');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('failedlogins', 'date_and_time', 'TIME');
INSERT INTO cms_db_meta (m_table, m_name, m_type) VALUES ('failedlogins', 'ip', 'IP');

ALTER TABLE cms10_db_meta ADD INDEX findtransfields (m_type);

DROP TABLE IF EXISTS cms_db_meta_indices;

CREATE TABLE cms_db_meta_indices (
    i_table varchar(80) NOT NULL,
    i_name varchar(80) NOT NULL,
    i_fields varchar(80) NOT NULL,
    PRIMARY KEY (i_table, i_name, i_fields)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('alternative_ids', 'resource_guid', 'resource_guid');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('alternative_ids', 'resource_label', 'resource_label');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('alternative_ids', 'resource_moniker', 'resource_moniker,resource_type');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('alternative_ids', 'resource_moniker_uniq', 'resource_moniker,resource_resource_fs_hook');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('attachments', 'attachmentlimitcheck', 'a_add_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('attachments', 'ownedattachments', 'a_member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('authors', '#description', 'description');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('authors', '#skills', 'skills');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('authors', 'findmemberlink', 'member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('autosave', 'myautosaves', 'a_member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cache', 'cached_ford', 'date_and_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cache', 'cached_fore', 'cached_for');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cache', 'cached_forf', 'cached_for,identifier,the_theme,lang,staff_status,the_member,is_bot');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cache', 'cached_forh', 'the_theme');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cached_comcode_pages', '#cc_page_title', 'cc_page_title');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cached_comcode_pages', '#page_search__combined', 'cc_page_title,string_index');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cached_comcode_pages', '#string_index', 'string_index');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cached_comcode_pages', 'ccp_join', 'the_page,the_zone');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cached_comcode_pages', 'ftjoin_ccpt', 'cc_page_title');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cached_comcode_pages', 'ftjoin_ccsi', 'string_index');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('captchas', 'si_time', 'si_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('comcode_pages', 'p_add_date', 'p_add_date');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('comcode_pages', 'p_order', 'p_order');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('comcode_pages', 'p_submitter', 'p_submitter');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('comcode_pages', 'p_validated', 'p_validated');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('config', '#c_value_trans', 'c_value_trans');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('content_privacy', 'friend_view', 'friend_view');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('content_privacy', 'guest_view', 'guest_view');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('content_privacy', 'member_view', 'member_view');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cron_caching_requests', 'c_compound', 'c_codename,c_theme,c_lang,c_timezone');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cron_caching_requests', 'c_is_bot', 'c_is_bot');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('cron_caching_requests', 'c_store_as_tempcode', 'c_store_as_tempcode');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('db_meta', 'findtransfields', 'm_type');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('digestives_tin', '#d_message', 'd_message');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('digestives_tin', 'd_date_and_time', 'd_date_and_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('digestives_tin', 'd_frequency', 'd_frequency');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('digestives_tin', 'd_read', 'd_read');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('digestives_tin', 'd_to_member_id', 'd_to_member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('digestives_tin', 'unread', 'd_to_member_id,d_read');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('edit_pings', 'edit_pings_on', 'the_page,the_type,the_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('email_bounces', 'b_email_address', 'b_email_address');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('email_bounces', 'b_time', 'b_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('failedlogins', 'failedlogins_by_ip', 'ip');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('filedump', '#description', 'description');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_custom_fields', '#cf_description', 'cf_description');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_custom_fields', '#cf_name', 'cf_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_emoticons', 'relevantemoticons', 'e_relevance_level');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_emoticons', 'topicemos', 'e_use_topics');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_forums', '#f_description', 'f_description');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_forums', '#f_intro_question', 'f_intro_question');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_forums', 'cache_num_posts', 'f_cache_num_posts');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_forums', 'findnamedforum', 'f_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_forums', 'f_position', 'f_position');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_forums', 'subforum_parenting', 'f_parent_forum');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', '#groups_search__combined', 'g_name,g_title');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', '#g_name', 'g_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', '#g_title', 'g_title');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'ftjoin_gname', 'g_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'ftjoin_gtitle', 'g_title');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'gorder', 'g_order,id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'hidden', 'g_hidden');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'is_default', 'g_is_default');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'is_presented_at_install', 'g_is_presented_at_install');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'is_private_club', 'g_is_private_club');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'is_super_admin', 'g_is_super_admin');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_groups', 'is_super_moderator', 'g_is_super_moderator');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_group_join_log', 'join_time', 'join_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_group_join_log', 'member_id', 'member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_group_join_log', 'usergroup_id', 'usergroup_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_group_members', 'gm_group_id', 'gm_group_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_group_members', 'gm_member_id', 'gm_member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_group_members', 'gm_validated', 'gm_validated');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', '#m_pt_rules_text', 'm_pt_rules_text');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', '#m_signature', 'm_signature');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', '#search_user', 'm_username');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'avatar_url', 'm_avatar_url');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'birthdays', 'm_dob_day,m_dob_month');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'external_auth_lookup', 'm_pass_hash_salted');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'ftjoin_msig', 'm_signature');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'last_visit_time', 'm_dob_month,m_dob_day,m_last_visit_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'menail', 'm_email_address');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'm_join_time', 'm_join_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'primary_group', 'm_primary_group');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'sort_post_count', 'm_cache_num_posts');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'user_list', 'm_username');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_members', 'whos_validated', 'm_validated');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', '#field_1', 'field_1');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', '#field_2', 'field_2');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', '#field_4', 'field_4');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', '#mcf_ft_3', 'field_3');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', '#mcf_ft_5', 'field_5');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', '#mcf_ft_6', 'field_6');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', '#mcf_ft_7', 'field_7');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', '#mcf_ft_8', 'field_8');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', 'mcf1', 'field_1');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', 'mcf2', 'field_2');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', 'mcf3', 'field_3');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', 'mcf4', 'field_4');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', 'mcf5', 'field_5');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', 'mcf6', 'field_6');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', 'mcf7', 'field_7');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_member_custom_fields', 'mcf8', 'field_8');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_multi_moderations', '#mm_name', 'mm_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_password_history', 'p_member_id', 'p_member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', '#posts_search__combined', 'p_post,p_title');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', '#p_post', 'p_post');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', '#p_title', 'p_title');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'deletebyip', 'p_ip_address');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'find_pp', 'p_intended_solely_for');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'in_topic', 'p_topic_id,p_time,id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'in_topic_change_order', 'p_topic_id,p_last_edit_time,p_time,id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'postsinforum', 'p_cache_forum_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'posts_by', 'p_poster,p_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'posts_by_in_forum', 'p_poster,p_cache_forum_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'posts_by_in_topic', 'p_poster,p_topic_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'posts_since', 'p_time,p_cache_forum_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'post_order_time', 'p_time,id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'p_last_edit_time', 'p_last_edit_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'p_validated', 'p_validated');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_posts', 'search_join', 'p_post');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_read_logs', 'erase_old_read_logs', 'l_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', '#t_cache_first_post', 't_cache_first_post');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', '#t_description', 't_description');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'descriptionsearch', 't_description');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'forumlayer', 't_cache_first_title');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'in_forum', 't_forum_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'ownedtopics', 't_cache_first_member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'topic_order', 't_cascading,t_pinned,t_cache_last_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'topic_order_2', 't_forum_id,t_cascading,t_pinned,t_sunk,t_cache_last_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'topic_order_3', 't_forum_id,t_cascading,t_pinned,t_cache_last_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'topic_order_time', 't_cache_last_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'topic_order_time_2', 't_cache_first_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_cache_first_post_id', 't_cache_first_post_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_cache_last_member_id', 't_cache_last_member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_cache_last_post_id', 't_cache_last_post_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_cache_num_posts', 't_cache_num_posts');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_cascading', 't_cascading');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_cascading_or_forum', 't_cascading,t_forum_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_num_views', 't_num_views');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_pt_from', 't_pt_from');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_pt_to', 't_pt_to');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 't_validated', 't_validated');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_topics', 'unread_forums', 't_forum_id,t_cache_last_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('f_warnings', 'warningsmemberid', 'w_member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('group_page_access', 'group_id', 'group_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('group_privileges', 'group_id', 'group_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('group_zone_access', 'group_id', 'group_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('link_tracker', 'c_url', 'c_url');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('logged_mail_messages', 'combo', 'm_date_and_time,m_queued');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('logged_mail_messages', 'queued', 'm_queued');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('logged_mail_messages', 'recentmessages', 'm_date_and_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('match_key_messages', '#k_message', 'k_message');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_category_access', 'mcamember_id', 'member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_category_access', 'mcaname', 'module_the_name,category_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_page_access', 'mzamember_id', 'member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_page_access', 'mzaname', 'page_name,zone_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_privileges', 'member_privileges_member', 'member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_privileges', 'member_privileges_name', 'privilege,the_page,module_the_name,category_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_tracking', 'mt_id', 'mt_page,mt_id,mt_type');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_tracking', 'mt_page', 'mt_page');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_tracking', 'mt_time', 'mt_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_zone_access', 'mzamember_id', 'member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('member_zone_access', 'mzazone_name', 'zone_name');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('menu_items', '#i_caption', 'i_caption');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('menu_items', '#i_caption_long', 'i_caption_long');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('menu_items', 'menu_extraction', 'i_menu');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('messages_to_render', 'forsession', 'r_session_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('notifications_enabled', 'l_code_category', 'l_code_category');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('notifications_enabled', 'l_member_id', 'l_member_id,l_notification_code');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('notifications_enabled', 'l_notification_code', 'l_notification_code');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('post_tokens', 'generation_time', 'generation_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('rating', 'alt_key', 'rating_for_type,rating_for_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('rating', 'rating_for_id', 'rating_for_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('review_supplement', 'rating_for_id', 'r_rating_for_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('seo_meta', '#meta_description', 'meta_description');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('seo_meta', 'alt_key', 'meta_for_type,meta_for_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('seo_meta', 'ftjoin_dmeta_description', 'meta_description');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('seo_meta_keywords', '#meta_keyword', 'meta_keyword');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('seo_meta_keywords', 'ftjoin_dmeta_keywords', 'meta_keyword');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('seo_meta_keywords', 'keywords_alt_key', 'meta_for_type,meta_for_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('sessions', 'delete_old', 'last_activity');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('sessions', 'member_id', 'member_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('sessions', 'userat', 'the_zone,the_page,the_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('sitemap_cache', 'is_deleted', 'is_deleted');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('sitemap_cache', 'last_updated', 'last_updated');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('sitemap_cache', 'set_number', 'set_number,last_updated');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('trackbacks', 'trackback_for_id', 'trackback_for_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('trackbacks', 'trackback_for_type', 'trackback_for_type');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('trackbacks', 'trackback_time', 'trackback_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('translate', '#tsearch', 'text_original');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('translate', 'decache', 'text_parsed(2)');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('translate', 'equiv_lang', 'text_original(4)');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('translate', 'importance_level', 'importance_level');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('urls_checked', 'url', 'url(200)');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('url_id_monikers', 'uim_moniker', 'm_moniker');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('url_id_monikers', 'uim_monrev', 'm_moniker_reversed');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('url_id_monikers', 'uim_page_link', 'm_resource_page,m_resource_type,m_resource_id');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('url_title_cache', 't_url', 't_url');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('values', 'date_and_time', 'date_and_time');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('zones', '#zone_header_text', 'zone_header_text');
INSERT INTO cms_db_meta_indices (i_table, i_name, i_fields) VALUES ('zones', '#zone_title', 'zone_title');

DROP TABLE IF EXISTS cms_digestives_consumed;

CREATE TABLE cms_digestives_consumed (
    c_member_id integer NOT NULL,
    c_frequency integer NOT NULL,
    c_time integer unsigned NOT NULL,
    PRIMARY KEY (c_member_id, c_frequency)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_digestives_tin;

CREATE TABLE cms_digestives_tin (
    id integer unsigned auto_increment NOT NULL,
    d_subject longtext NOT NULL,
    d_message longtext NOT NULL,
    d_from_member_id integer NULL,
    d_to_member_id integer NOT NULL,
    d_priority tinyint NOT NULL,
    d_no_cc tinyint(1) NOT NULL,
    d_date_and_time integer unsigned NOT NULL,
    d_notification_code varchar(80) NOT NULL,
    d_code_category varchar(255) NOT NULL,
    d_frequency integer NOT NULL,
    d_read tinyint(1) NOT NULL,
    d_message__text_parsed longtext NOT NULL,
    d_message__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_digestives_tin ADD FULLTEXT d_message (d_message);

ALTER TABLE cms10_digestives_tin ADD INDEX d_date_and_time (d_date_and_time);

ALTER TABLE cms10_digestives_tin ADD INDEX d_frequency (d_frequency);

ALTER TABLE cms10_digestives_tin ADD INDEX d_read (d_read);

ALTER TABLE cms10_digestives_tin ADD INDEX d_to_member_id (d_to_member_id);

ALTER TABLE cms10_digestives_tin ADD INDEX unread (d_to_member_id,d_read);

DROP TABLE IF EXISTS cms_edit_pings;

CREATE TABLE cms_edit_pings (
    id integer unsigned auto_increment NOT NULL,
    the_page varchar(80) NOT NULL,
    the_type varchar(80) NOT NULL,
    the_id varchar(80) NOT NULL,
    the_time integer unsigned NOT NULL,
    the_member integer NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_edit_pings ADD INDEX edit_pings_on (the_page,the_type,the_id);

DROP TABLE IF EXISTS cms_email_bounces;

CREATE TABLE cms_email_bounces (
    id integer unsigned auto_increment NOT NULL,
    b_email_address varchar(255) NOT NULL,
    b_time integer unsigned NOT NULL,
    b_subject varchar(255) NOT NULL,
    b_body longtext NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_email_bounces ADD INDEX b_email_address (b_email_address(250));

ALTER TABLE cms10_email_bounces ADD INDEX b_time (b_time);

DROP TABLE IF EXISTS cms_f_custom_fields;

CREATE TABLE cms_f_custom_fields (
    id integer unsigned auto_increment NOT NULL,
    cf_locked tinyint(1) NOT NULL,
    cf_name longtext NOT NULL,
    cf_description longtext NOT NULL,
    cf_default longtext NOT NULL,
    cf_public_view tinyint(1) NOT NULL,
    cf_owner_view tinyint(1) NOT NULL,
    cf_owner_set tinyint(1) NOT NULL,
    cf_type varchar(80) NOT NULL,
    cf_required tinyint(1) NOT NULL,
    cf_show_in_posts tinyint(1) NOT NULL,
    cf_show_in_post_previews tinyint(1) NOT NULL,
    cf_order integer NOT NULL,
    cf_only_group longtext NOT NULL,
    cf_encrypted tinyint(1) NOT NULL,
    cf_show_on_join_form tinyint(1) NOT NULL,
    cf_options varchar(255) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (1, 0, 'About me', 'Some personally written information.', '', 1, 1, 1, 'long_trans', 0, 0, 0, 0, '', 0, 0, '');
INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (2, 0, 'Interests', 'A summary of your interests.', '', 1, 1, 1, 'long_trans', 0, 1, 1, 1, '', 0, 0, '');
INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (3, 0, 'Occupation', 'Your occupation.', '', 1, 1, 1, 'short_text', 0, 0, 0, 2, '', 0, 0, '');
INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (4, 0, 'Staff notes', 'Notes on this member, only viewable by staff.', '', 0, 0, 0, 'long_trans', 0, 0, 0, 3, '', 0, 0, '');
INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (5, 0, 'Skype ID', 'Your Skype ID.', '', 1, 1, 1, 'short_text', 0, 0, 0, 4, '', 0, 0, '');
INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (6, 0, 'Facebook profile', 'A link to your Facebook profile.', '', 1, 1, 1, 'short_text', 0, 0, 0, 5, '', 0, 0, '');
INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (7, 0, 'Google+ profile', 'A link to your Google+ profile.', '', 1, 1, 1, 'short_text', 0, 0, 0, 6, '', 0, 0, '');
INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (8, 0, 'Twitter account', 'Your Twitter name (for example, \'charlie12345\').', '', 1, 1, 1, 'short_text', 0, 0, 0, 7, '', 0, 0, '');
INSERT INTO cms_f_custom_fields (id, cf_locked, cf_name, cf_description, cf_default, cf_public_view, cf_owner_view, cf_owner_set, cf_type, cf_required, cf_show_in_posts, cf_show_in_post_previews, cf_order, cf_only_group, cf_encrypted, cf_show_on_join_form, cf_options) VALUES (9, 1, 'cms_smart_topic_notification', '', '0', 0, 0, 1, 'tick', 1, 0, 0, 8, '', 0, 0, '');

ALTER TABLE cms10_f_custom_fields ADD FULLTEXT cf_description (cf_description);

ALTER TABLE cms10_f_custom_fields ADD FULLTEXT cf_name (cf_name);

DROP TABLE IF EXISTS cms_f_emoticons;

CREATE TABLE cms_f_emoticons (
    e_code varchar(80) NOT NULL,
    e_theme_img_code varchar(255) NOT NULL,
    e_relevance_level integer NOT NULL,
    e_use_topics tinyint(1) NOT NULL,
    e_is_special tinyint(1) NOT NULL,
    PRIMARY KEY (e_code)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':P', 'cns_emoticons/cheeky', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':\'(', 'cns_emoticons/cry', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':dry:', 'cns_emoticons/dry', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':$', 'cns_emoticons/blush', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (';)', 'cns_emoticons/wink', 0, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES ('O_o', 'cns_emoticons/blink', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':wub:', 'cns_emoticons/wub', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':cool:', 'cns_emoticons/cool', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':lol:', 'cns_emoticons/lol', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':(', 'cns_emoticons/sad', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':)', 'cns_emoticons/smile', 0, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':thumbs:', 'cns_emoticons/thumbs', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':|', 'cns_emoticons/mellow', 0, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':ninja:', 'cns_emoticons/ph34r', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':o', 'cns_emoticons/shocked', 0, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':offtopic:', 'cns_emoticons/offtopic', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':rolleyes:', 'cns_emoticons/rolleyes', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':D', 'cns_emoticons/grin', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES ('^_^', 'cns_emoticons/glee', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES ('(K)', 'cns_emoticons/kiss', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':S', 'cns_emoticons/confused', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':@', 'cns_emoticons/angry', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':shake:', 'cns_emoticons/shake', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':hand:', 'cns_emoticons/hand', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':drool:', 'cns_emoticons/drool', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':devil:', 'cns_emoticons/devil', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':party:', 'cns_emoticons/party', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':constipated:', 'cns_emoticons/constipated', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':depressed:', 'cns_emoticons/depressed', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':zzz:', 'cns_emoticons/zzz', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':whistle:', 'cns_emoticons/whistle', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':upsidedown:', 'cns_emoticons/upsidedown', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':sick:', 'cns_emoticons/sick', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':shutup:', 'cns_emoticons/shutup', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':sarcy:', 'cns_emoticons/sarcy', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':puppyeyes:', 'cns_emoticons/puppyeyes', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':nod:', 'cns_emoticons/nod', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':nerd:', 'cns_emoticons/nerd', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':king:', 'cns_emoticons/king', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':birthday:', 'cns_emoticons/birthday', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':cyborg:', 'cns_emoticons/cyborg', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':hippie:', 'cns_emoticons/hippie', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':ninja2:', 'cns_emoticons/ninja2', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':rockon:', 'cns_emoticons/rockon', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':sinner:', 'cns_emoticons/sinner', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':guitar:', 'cns_emoticons/guitar', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':angel:', 'cns_emoticons/angel', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':cowboy:', 'cns_emoticons/cowboy', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':fight:', 'cns_emoticons/fight', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':goodbye:', 'cns_emoticons/goodbye', 1, 1, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':idea:', 'cns_emoticons/idea', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':boat:', 'cns_emoticons/boat', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':fishing:', 'cns_emoticons/fishing', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':reallybadday:', 'cns_emoticons/reallybadday', 1, 0, 0);
INSERT INTO cms_f_emoticons (e_code, e_theme_img_code, e_relevance_level, e_use_topics, e_is_special) VALUES (':christmas:', 'cns_emoticons/christmas', 1, 0, 0);

ALTER TABLE cms10_f_emoticons ADD INDEX relevantemoticons (e_relevance_level);

ALTER TABLE cms10_f_emoticons ADD INDEX topicemos (e_use_topics);

DROP TABLE IF EXISTS cms_f_forum_groupings;

CREATE TABLE cms_f_forum_groupings (
    id integer unsigned auto_increment NOT NULL,
    c_title varchar(255) NOT NULL,
    c_description longtext NOT NULL,
    c_expanded_by_default tinyint(1) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_forum_groupings (id, c_title, c_description, c_expanded_by_default) VALUES (1, 'General', '', 1);
INSERT INTO cms_f_forum_groupings (id, c_title, c_description, c_expanded_by_default) VALUES (2, 'Staff', '', 1);

DROP TABLE IF EXISTS cms_f_forum_intro_ip;

CREATE TABLE cms_f_forum_intro_ip (
    i_forum_id integer NOT NULL,
    i_ip varchar(40) NOT NULL,
    PRIMARY KEY (i_forum_id, i_ip)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_forum_intro_member;

CREATE TABLE cms_f_forum_intro_member (
    i_forum_id integer NOT NULL,
    i_member_id integer NOT NULL,
    PRIMARY KEY (i_forum_id, i_member_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_forums;

CREATE TABLE cms_f_forums (
    id integer unsigned auto_increment NOT NULL,
    f_name varchar(255) NOT NULL,
    f_description longtext NOT NULL,
    f_forum_grouping_id integer NULL,
    f_parent_forum integer NULL,
    f_position integer NOT NULL,
    f_order_sub_alpha tinyint(1) NOT NULL,
    f_post_count_increment tinyint(1) NOT NULL,
    f_intro_question longtext NOT NULL,
    f_intro_answer varchar(255) NOT NULL,
    f_cache_num_topics integer NOT NULL,
    f_cache_num_posts integer NOT NULL,
    f_cache_last_topic_id integer NULL,
    f_cache_last_title varchar(255) NOT NULL,
    f_cache_last_time integer unsigned NULL,
    f_cache_last_username varchar(255) NOT NULL,
    f_cache_last_member_id integer NULL,
    f_cache_last_forum_id integer NULL,
    f_redirection varchar(255) NOT NULL,
    f_order varchar(80) NOT NULL,
    f_is_threaded tinyint(1) NOT NULL,
    f_allows_anonymous_posts tinyint(1) NOT NULL,
    f_description__text_parsed longtext NOT NULL,
    f_description__source_user integer DEFAULT 1 NOT NULL,
    f_intro_question__text_parsed longtext NOT NULL,
    f_intro_question__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_forums (id, f_name, f_description, f_forum_grouping_id, f_parent_forum, f_position, f_order_sub_alpha, f_post_count_increment, f_intro_question, f_intro_answer, f_cache_num_topics, f_cache_num_posts, f_cache_last_topic_id, f_cache_last_title, f_cache_last_time, f_cache_last_username, f_cache_last_member_id, f_cache_last_forum_id, f_redirection, f_order, f_is_threaded, f_allows_anonymous_posts, f_description__text_parsed, f_description__source_user, f_intro_question__text_parsed, f_intro_question__source_user) VALUES (1, 'Forum home', '', NULL, NULL, 1, 0, 1, '', '', 0, 0, NULL, '', NULL, '', NULL, NULL, '', 'last_post', 0, 0, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_1\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_1\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_1\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_2\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_2\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_2\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);
INSERT INTO cms_f_forums (id, f_name, f_description, f_forum_grouping_id, f_parent_forum, f_position, f_order_sub_alpha, f_post_count_increment, f_intro_question, f_intro_answer, f_cache_num_topics, f_cache_num_posts, f_cache_last_topic_id, f_cache_last_title, f_cache_last_time, f_cache_last_username, f_cache_last_member_id, f_cache_last_forum_id, f_redirection, f_order, f_is_threaded, f_allows_anonymous_posts, f_description__text_parsed, f_description__source_user, f_intro_question__text_parsed, f_intro_question__source_user) VALUES (2, 'General chat', '', 1, 1, 1, 0, 1, '', '', 0, 0, NULL, '', NULL, '', NULL, NULL, '', 'last_post', 0, 0, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_3\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_3\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_3\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_4\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_4\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_4\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);
INSERT INTO cms_f_forums (id, f_name, f_description, f_forum_grouping_id, f_parent_forum, f_position, f_order_sub_alpha, f_post_count_increment, f_intro_question, f_intro_answer, f_cache_num_topics, f_cache_num_posts, f_cache_last_topic_id, f_cache_last_title, f_cache_last_time, f_cache_last_username, f_cache_last_member_id, f_cache_last_forum_id, f_redirection, f_order, f_is_threaded, f_allows_anonymous_posts, f_description__text_parsed, f_description__source_user, f_intro_question__text_parsed, f_intro_question__source_user) VALUES (3, 'Reported posts forum', '', 2, 1, 1, 0, 1, '', '', 0, 0, NULL, '', NULL, '', NULL, NULL, '', 'last_post', 0, 0, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_5\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_5\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_5\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_6\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_6\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_6\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);
INSERT INTO cms_f_forums (id, f_name, f_description, f_forum_grouping_id, f_parent_forum, f_position, f_order_sub_alpha, f_post_count_increment, f_intro_question, f_intro_answer, f_cache_num_topics, f_cache_num_posts, f_cache_last_topic_id, f_cache_last_title, f_cache_last_time, f_cache_last_username, f_cache_last_member_id, f_cache_last_forum_id, f_redirection, f_order, f_is_threaded, f_allows_anonymous_posts, f_description__text_parsed, f_description__source_user, f_intro_question__text_parsed, f_intro_question__source_user) VALUES (4, 'Trash', '', 2, 1, 1, 0, 1, '', '', 0, 0, NULL, '', NULL, '', NULL, NULL, '', 'last_post', 0, 0, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_7\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_7\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_7\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_8\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_8\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_8\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);
INSERT INTO cms_f_forums (id, f_name, f_description, f_forum_grouping_id, f_parent_forum, f_position, f_order_sub_alpha, f_post_count_increment, f_intro_question, f_intro_answer, f_cache_num_topics, f_cache_num_posts, f_cache_last_topic_id, f_cache_last_title, f_cache_last_time, f_cache_last_username, f_cache_last_member_id, f_cache_last_forum_id, f_redirection, f_order, f_is_threaded, f_allows_anonymous_posts, f_description__text_parsed, f_description__source_user, f_intro_question__text_parsed, f_intro_question__source_user) VALUES (5, 'Website comment topics', '', 1, 1, 1, 0, 1, '', '', 0, 0, NULL, '', NULL, '', NULL, NULL, '', 'last_post', 1, 0, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19c21be63.35171687_9\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:39:\\\"string_attach_5ad7d19c21be63.35171687_9\\\";s:68:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_9\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_10\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_10\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_10\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);
INSERT INTO cms_f_forums (id, f_name, f_description, f_forum_grouping_id, f_parent_forum, f_position, f_order_sub_alpha, f_post_count_increment, f_intro_question, f_intro_answer, f_cache_num_topics, f_cache_num_posts, f_cache_last_topic_id, f_cache_last_title, f_cache_last_time, f_cache_last_username, f_cache_last_member_id, f_cache_last_forum_id, f_redirection, f_order, f_is_threaded, f_allows_anonymous_posts, f_description__text_parsed, f_description__source_user, f_intro_question__text_parsed, f_intro_question__source_user) VALUES (6, 'Website support tickets', '', 2, 1, 1, 0, 1, '', '', 0, 0, NULL, '', NULL, '', NULL, NULL, '', 'last_post', 0, 0, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_11\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_11\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_11\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_12\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_12\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_12\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);
INSERT INTO cms_f_forums (id, f_name, f_description, f_forum_grouping_id, f_parent_forum, f_position, f_order_sub_alpha, f_post_count_increment, f_intro_question, f_intro_answer, f_cache_num_topics, f_cache_num_posts, f_cache_last_topic_id, f_cache_last_title, f_cache_last_time, f_cache_last_username, f_cache_last_member_id, f_cache_last_forum_id, f_redirection, f_order, f_is_threaded, f_allows_anonymous_posts, f_description__text_parsed, f_description__source_user, f_intro_question__text_parsed, f_intro_question__source_user) VALUES (7, 'Staff', '', 2, 1, 1, 0, 1, '', '', 1, 1, 1, 'Welcome to the forums', 1524093340, 'System', 1, 7, '', 'last_post', 0, 0, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_13\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_13\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_13\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_14\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_14\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_14\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);

ALTER TABLE cms10_f_forums ADD FULLTEXT f_description (f_description);

ALTER TABLE cms10_f_forums ADD FULLTEXT f_intro_question (f_intro_question);

ALTER TABLE cms10_f_forums ADD INDEX cache_num_posts (f_cache_num_posts);

ALTER TABLE cms10_f_forums ADD INDEX findnamedforum (f_name(250));

ALTER TABLE cms10_f_forums ADD INDEX f_position (f_position);

ALTER TABLE cms10_f_forums ADD INDEX subforum_parenting (f_parent_forum);

DROP TABLE IF EXISTS cms_f_group_join_log;

CREATE TABLE cms_f_group_join_log (
    id integer unsigned auto_increment NOT NULL,
    member_id integer NOT NULL,
    usergroup_id integer NULL,
    join_time integer unsigned NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_f_group_join_log ADD INDEX join_time (join_time);

ALTER TABLE cms10_f_group_join_log ADD INDEX member_id (member_id);

ALTER TABLE cms10_f_group_join_log ADD INDEX usergroup_id (usergroup_id);

DROP TABLE IF EXISTS cms_f_group_member_timeouts;

CREATE TABLE cms_f_group_member_timeouts (
    member_id integer NOT NULL,
    group_id integer NOT NULL,
    timeout integer unsigned NOT NULL,
    PRIMARY KEY (member_id, group_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_group_members;

CREATE TABLE cms_f_group_members (
    gm_group_id integer NOT NULL,
    gm_member_id integer NOT NULL,
    gm_validated tinyint(1) NOT NULL,
    PRIMARY KEY (gm_group_id, gm_member_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_f_group_members ADD INDEX gm_group_id (gm_group_id);

ALTER TABLE cms10_f_group_members ADD INDEX gm_member_id (gm_member_id);

ALTER TABLE cms10_f_group_members ADD INDEX gm_validated (gm_validated);

DROP TABLE IF EXISTS cms_f_groups;

CREATE TABLE cms_f_groups (
    id integer unsigned auto_increment NOT NULL,
    g_name longtext NOT NULL,
    g_is_default tinyint(1) NOT NULL,
    g_is_presented_at_install tinyint(1) NOT NULL,
    g_is_super_admin tinyint(1) NOT NULL,
    g_is_super_moderator tinyint(1) NOT NULL,
    g_group_leader integer NULL,
    g_title longtext NOT NULL,
    g_promotion_target integer NULL,
    g_promotion_threshold integer NULL,
    g_flood_control_submit_secs integer NOT NULL,
    g_flood_control_access_secs integer NOT NULL,
    g_gift_points_base integer NOT NULL,
    g_gift_points_per_day integer NOT NULL,
    g_max_daily_upload_mb integer NOT NULL,
    g_max_attachments_per_post integer NOT NULL,
    g_max_avatar_width integer NOT NULL,
    g_max_avatar_height integer NOT NULL,
    g_max_post_length_comcode integer NOT NULL,
    g_max_sig_length_comcode integer NOT NULL,
    g_enquire_on_new_ips tinyint(1) NOT NULL,
    g_rank_image varchar(80) NOT NULL,
    g_hidden tinyint(1) NOT NULL,
    g_order integer NOT NULL,
    g_rank_image_pri_only tinyint(1) NOT NULL,
    g_open_membership tinyint(1) NOT NULL,
    g_is_private_club tinyint(1) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (1, 'Guests', 0, 0, 0, 0, NULL, 'Guest user', NULL, NULL, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, '', 0, 0, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (2, 'Administrators', 0, 0, 1, 0, NULL, 'Site director', NULL, NULL, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, 'cns_rank_images/admin', 0, 1, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (3, 'Super-moderators', 0, 0, 0, 1, NULL, 'Site staff', NULL, NULL, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, 'cns_rank_images/mod', 0, 2, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (4, 'Super-members', 0, 0, 0, 0, NULL, 'Super-member', NULL, NULL, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, '', 0, 3, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (5, 'Local hero', 0, 0, 0, 0, NULL, 'Standard member', NULL, NULL, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, 'cns_rank_images/4', 0, 4, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (6, 'Old timer', 0, 0, 0, 0, NULL, 'Standard member', 5, 10000, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, 'cns_rank_images/3', 0, 5, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (7, 'Local', 0, 0, 0, 0, NULL, 'Standard member', 6, 2500, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, 'cns_rank_images/2', 0, 6, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (8, 'Regular', 0, 0, 0, 0, NULL, 'Standard member', 7, 400, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, 'cns_rank_images/1', 0, 7, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (9, 'Newbie', 0, 0, 0, 0, NULL, 'Standard member', 8, 100, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, 'cns_rank_images/0', 0, 8, 1, 0, 0);
INSERT INTO cms_f_groups (id, g_name, g_is_default, g_is_presented_at_install, g_is_super_admin, g_is_super_moderator, g_group_leader, g_title, g_promotion_target, g_promotion_threshold, g_flood_control_submit_secs, g_flood_control_access_secs, g_gift_points_base, g_gift_points_per_day, g_max_daily_upload_mb, g_max_attachments_per_post, g_max_avatar_width, g_max_avatar_height, g_max_post_length_comcode, g_max_sig_length_comcode, g_enquire_on_new_ips, g_rank_image, g_hidden, g_order, g_rank_image_pri_only, g_open_membership, g_is_private_club) VALUES (10, 'Probation', 0, 0, 0, 0, NULL, 'Placed on temporary probation', NULL, NULL, 0, 0, 25, 1, 70, 50, 100, 100, 30000, 700, 0, '', 0, 9, 1, 0, 0);

ALTER TABLE cms10_f_groups ADD FULLTEXT groups_search__combined (g_name,g_title);

ALTER TABLE cms10_f_groups ADD FULLTEXT g_name (g_name);

ALTER TABLE cms10_f_groups ADD FULLTEXT g_title (g_title);

ALTER TABLE cms10_f_groups ADD INDEX ftjoin_gname (g_name(250));

ALTER TABLE cms10_f_groups ADD INDEX ftjoin_gtitle (g_title(250));

ALTER TABLE cms10_f_groups ADD INDEX gorder (g_order,id);

ALTER TABLE cms10_f_groups ADD INDEX hidden (g_hidden);

ALTER TABLE cms10_f_groups ADD INDEX is_default (g_is_default);

ALTER TABLE cms10_f_groups ADD INDEX is_presented_at_install (g_is_presented_at_install);

ALTER TABLE cms10_f_groups ADD INDEX is_private_club (g_is_private_club);

ALTER TABLE cms10_f_groups ADD INDEX is_super_admin (g_is_super_admin);

ALTER TABLE cms10_f_groups ADD INDEX is_super_moderator (g_is_super_moderator);

DROP TABLE IF EXISTS cms_f_invites;

CREATE TABLE cms_f_invites (
    id integer unsigned auto_increment NOT NULL,
    i_inviter integer NOT NULL,
    i_email_address varchar(255) NOT NULL,
    i_time integer unsigned NOT NULL,
    i_taken tinyint(1) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_member_cpf_perms;

CREATE TABLE cms_f_member_cpf_perms (
    member_id integer NOT NULL,
    field_id integer NOT NULL,
    guest_view tinyint(1) NOT NULL,
    member_view tinyint(1) NOT NULL,
    friend_view tinyint(1) NOT NULL,
    group_view varchar(255) NOT NULL,
    PRIMARY KEY (member_id, field_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_member_custom_fields;

CREATE TABLE cms_f_member_custom_fields (
    mf_member_id integer NOT NULL,
    field_1 longtext NOT NULL,
    field_2 longtext NOT NULL,
    field_3 varchar(255) NOT NULL,
    field_4 longtext NOT NULL,
    field_5 varchar(255) NOT NULL,
    field_6 varchar(255) NOT NULL,
    field_7 varchar(255) NOT NULL,
    field_8 varchar(255) NOT NULL,
    field_9 integer NULL,
    field_1__text_parsed longtext NOT NULL,
    field_1__source_user integer DEFAULT 1 NOT NULL,
    field_2__text_parsed longtext NOT NULL,
    field_2__source_user integer DEFAULT 1 NOT NULL,
    field_4__text_parsed longtext NOT NULL,
    field_4__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (mf_member_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_member_custom_fields (mf_member_id, field_1, field_1__text_parsed, field_1__source_user, field_2, field_2__text_parsed, field_2__source_user, field_3, field_4, field_4__text_parsed, field_4__source_user, field_5, field_6, field_7, field_8, field_9) VALUES (1, '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_17\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_17\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_17\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_18\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_18\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_18\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_19\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_19\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_19\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', '', '', '', 0);
INSERT INTO cms_f_member_custom_fields (mf_member_id, field_1, field_1__text_parsed, field_1__source_user, field_2, field_2__text_parsed, field_2__source_user, field_3, field_4, field_4__text_parsed, field_4__source_user, field_5, field_6, field_7, field_8, field_9) VALUES (2, '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_22\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_22\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_22\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_23\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_23\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_23\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_24\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_24\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_24\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', '', '', '', 0);
INSERT INTO cms_f_member_custom_fields (mf_member_id, field_1, field_1__text_parsed, field_1__source_user, field_2, field_2__text_parsed, field_2__source_user, field_3, field_4, field_4__text_parsed, field_4__source_user, field_5, field_6, field_7, field_8, field_9) VALUES (3, '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_27\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_27\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_27\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_28\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_28\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_28\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', '', 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_29\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_29\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_29\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, '', '', '', '', 0);

ALTER TABLE cms10_f_member_custom_fields ADD FULLTEXT field_1 (field_1);

ALTER TABLE cms10_f_member_custom_fields ADD FULLTEXT field_2 (field_2);

ALTER TABLE cms10_f_member_custom_fields ADD FULLTEXT field_4 (field_4);

ALTER TABLE cms10_f_member_custom_fields ADD FULLTEXT mcf_ft_3 (field_3);

ALTER TABLE cms10_f_member_custom_fields ADD FULLTEXT mcf_ft_5 (field_5);

ALTER TABLE cms10_f_member_custom_fields ADD FULLTEXT mcf_ft_6 (field_6);

ALTER TABLE cms10_f_member_custom_fields ADD FULLTEXT mcf_ft_7 (field_7);

ALTER TABLE cms10_f_member_custom_fields ADD FULLTEXT mcf_ft_8 (field_8);

ALTER TABLE cms10_f_member_custom_fields ADD INDEX mcf1 (field_1(250));

ALTER TABLE cms10_f_member_custom_fields ADD INDEX mcf2 (field_2(250));

ALTER TABLE cms10_f_member_custom_fields ADD INDEX mcf3 (field_3(250));

ALTER TABLE cms10_f_member_custom_fields ADD INDEX mcf4 (field_4(250));

ALTER TABLE cms10_f_member_custom_fields ADD INDEX mcf5 (field_5(250));

ALTER TABLE cms10_f_member_custom_fields ADD INDEX mcf6 (field_6(250));

ALTER TABLE cms10_f_member_custom_fields ADD INDEX mcf7 (field_7(250));

ALTER TABLE cms10_f_member_custom_fields ADD INDEX mcf8 (field_8(250));

DROP TABLE IF EXISTS cms_f_member_known_login_ips;

CREATE TABLE cms_f_member_known_login_ips (
    i_member_id integer NOT NULL,
    i_ip varchar(40) NOT NULL,
    i_val_code varchar(255) NOT NULL,
    PRIMARY KEY (i_member_id, i_ip)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_members;

CREATE TABLE cms_f_members (
    id integer unsigned auto_increment NOT NULL,
    m_username varchar(80) NOT NULL,
    m_pass_hash_salted varchar(255) NOT NULL,
    m_pass_salt varchar(255) NOT NULL,
    m_theme varchar(80) NOT NULL,
    m_avatar_url varchar(255) BINARY NOT NULL,
    m_validated tinyint(1) NOT NULL,
    m_validated_email_confirm_code varchar(255) NOT NULL,
    m_cache_num_posts integer NOT NULL,
    m_cache_warnings integer NOT NULL,
    m_join_time integer unsigned NOT NULL,
    m_timezone_offset varchar(255) NOT NULL,
    m_primary_group integer NOT NULL,
    m_last_visit_time integer unsigned NOT NULL,
    m_last_submit_time integer unsigned NOT NULL,
    m_signature longtext NOT NULL,
    m_is_perm_banned tinyint(1) NOT NULL,
    m_preview_posts tinyint(1) NOT NULL,
    m_dob_day tinyint NULL,
    m_dob_month tinyint NULL,
    m_dob_year integer NULL,
    m_reveal_age tinyint(1) NOT NULL,
    m_email_address varchar(255) NOT NULL,
    m_title varchar(255) NOT NULL,
    m_photo_url varchar(255) BINARY NOT NULL,
    m_photo_thumb_url varchar(255) BINARY NOT NULL,
    m_views_signatures tinyint(1) NOT NULL,
    m_auto_monitor_contrib_content tinyint(1) NOT NULL,
    m_language varchar(80) NOT NULL,
    m_ip_address varchar(40) NOT NULL,
    m_allow_emails tinyint(1) NOT NULL,
    m_allow_emails_from_staff tinyint(1) NOT NULL,
    m_highlighted_name tinyint(1) NOT NULL,
    m_pt_allow varchar(255) NOT NULL,
    m_pt_rules_text longtext NOT NULL,
    m_max_email_attach_size_mb integer NOT NULL,
    m_password_change_code varchar(255) NOT NULL,
    m_password_compat_scheme varchar(80) NOT NULL,
    m_on_probation_until integer unsigned NULL,
    m_profile_views integer unsigned NOT NULL,
    m_total_sessions integer unsigned NOT NULL,
    m_auto_mark_read tinyint(1) NOT NULL,
    m_signature__text_parsed longtext NOT NULL,
    m_signature__source_user integer DEFAULT 1 NOT NULL,
    m_pt_rules_text__text_parsed longtext NOT NULL,
    m_pt_rules_text__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_members (id, m_username, m_pass_hash_salted, m_pass_salt, m_theme, m_avatar_url, m_validated, m_validated_email_confirm_code, m_cache_num_posts, m_cache_warnings, m_join_time, m_timezone_offset, m_primary_group, m_last_visit_time, m_last_submit_time, m_signature, m_is_perm_banned, m_preview_posts, m_dob_day, m_dob_month, m_dob_year, m_reveal_age, m_email_address, m_title, m_photo_url, m_photo_thumb_url, m_views_signatures, m_auto_monitor_contrib_content, m_language, m_ip_address, m_allow_emails, m_allow_emails_from_staff, m_highlighted_name, m_pt_allow, m_pt_rules_text, m_max_email_attach_size_mb, m_password_change_code, m_password_compat_scheme, m_on_probation_until, m_profile_views, m_total_sessions, m_auto_mark_read, m_signature__text_parsed, m_signature__source_user, m_pt_rules_text__text_parsed, m_pt_rules_text__source_user) VALUES (1, 'Guest', '', '', '', '', 1, '', 0, 0, 1524093340, 'UTC', 1, 1524093340, 1524093340, '', 0, 1, NULL, NULL, NULL, 1, '', '', '', '', 1, 0, '', '127.0.0.1', 1, 1, 0, '*', '', 5, '', 'plain', NULL, 0, 0, 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_15\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_15\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_15\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_16\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_16\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_16\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);
INSERT INTO cms_f_members (id, m_username, m_pass_hash_salted, m_pass_salt, m_theme, m_avatar_url, m_validated, m_validated_email_confirm_code, m_cache_num_posts, m_cache_warnings, m_join_time, m_timezone_offset, m_primary_group, m_last_visit_time, m_last_submit_time, m_signature, m_is_perm_banned, m_preview_posts, m_dob_day, m_dob_month, m_dob_year, m_reveal_age, m_email_address, m_title, m_photo_url, m_photo_thumb_url, m_views_signatures, m_auto_monitor_contrib_content, m_language, m_ip_address, m_allow_emails, m_allow_emails_from_staff, m_highlighted_name, m_pt_allow, m_pt_rules_text, m_max_email_attach_size_mb, m_password_change_code, m_password_compat_scheme, m_on_probation_until, m_profile_views, m_total_sessions, m_auto_mark_read, m_signature__text_parsed, m_signature__source_user, m_pt_rules_text__text_parsed, m_pt_rules_text__source_user) VALUES (2, 'admin', '', '', '', 'themes/default/images/cns_default_avatars/default_set/cool_flare.png', 1, '', 0, 0, 1524093340, 'UTC', 2, 1524093340, 1524093340, '', 0, 0, NULL, NULL, NULL, 1, '', '', '', '', 1, 1, '', '127.0.0.1', 1, 1, 0, '*', '', 5, '', 'plain', NULL, 0, 0, 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_20\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_20\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_20\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_21\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_21\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_21\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);
INSERT INTO cms_f_members (id, m_username, m_pass_hash_salted, m_pass_salt, m_theme, m_avatar_url, m_validated, m_validated_email_confirm_code, m_cache_num_posts, m_cache_warnings, m_join_time, m_timezone_offset, m_primary_group, m_last_visit_time, m_last_submit_time, m_signature, m_is_perm_banned, m_preview_posts, m_dob_day, m_dob_month, m_dob_year, m_reveal_age, m_email_address, m_title, m_photo_url, m_photo_thumb_url, m_views_signatures, m_auto_monitor_contrib_content, m_language, m_ip_address, m_allow_emails, m_allow_emails_from_staff, m_highlighted_name, m_pt_allow, m_pt_rules_text, m_max_email_attach_size_mb, m_password_change_code, m_password_compat_scheme, m_on_probation_until, m_profile_views, m_total_sessions, m_auto_mark_read, m_signature__text_parsed, m_signature__source_user, m_pt_rules_text__text_parsed, m_pt_rules_text__source_user) VALUES (3, 'test', '', '', '', '', 1, '', 0, 0, 1524093340, 'UTC', 9, 1524093340, 1524093340, '', 0, 0, NULL, NULL, NULL, 1, '', '', '', '', 1, 0, '', '127.0.0.1', 1, 1, 0, '*', '', 5, '', 'plain', NULL, 0, 0, 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_25\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_25\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_25\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:1:{i:0;a:5:{i:0;s:40:\\\"string_attach_5ad7d19c21be63.35171687_26\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:10:\\\":container\\\";i:3;N;i:4;a:1:{s:40:\\\"string_attach_5ad7d19c21be63.35171687_26\\\";s:69:\\\"\\$tpl_funcs[\'string_attach_5ad7d19c21be63.35171687_26\']=\\\"echo \\\\\\\"\\\\\\\";\\\";\\n\\\";}}\");
', 1);

ALTER TABLE cms10_f_members ADD FULLTEXT m_pt_rules_text (m_pt_rules_text);

ALTER TABLE cms10_f_members ADD FULLTEXT m_signature (m_signature);

ALTER TABLE cms10_f_members ADD FULLTEXT search_user (m_username);

ALTER TABLE cms10_f_members ADD INDEX avatar_url (m_avatar_url(250));

ALTER TABLE cms10_f_members ADD INDEX birthdays (m_dob_day,m_dob_month);

ALTER TABLE cms10_f_members ADD INDEX external_auth_lookup (m_pass_hash_salted(250));

ALTER TABLE cms10_f_members ADD INDEX ftjoin_msig (m_signature(250));

ALTER TABLE cms10_f_members ADD INDEX last_visit_time (m_dob_month,m_dob_day,m_last_visit_time);

ALTER TABLE cms10_f_members ADD INDEX menail (m_email_address(250));

ALTER TABLE cms10_f_members ADD INDEX m_join_time (m_join_time);

ALTER TABLE cms10_f_members ADD INDEX primary_group (m_primary_group);

ALTER TABLE cms10_f_members ADD INDEX sort_post_count (m_cache_num_posts);

ALTER TABLE cms10_f_members ADD INDEX user_list (m_username);

ALTER TABLE cms10_f_members ADD INDEX whos_validated (m_validated);

DROP TABLE IF EXISTS cms_f_moderator_logs;

CREATE TABLE cms_f_moderator_logs (
    id integer unsigned auto_increment NOT NULL,
    l_the_type varchar(80) NOT NULL,
    l_param_a varchar(255) NOT NULL,
    l_param_b varchar(255) NOT NULL,
    l_date_and_time integer unsigned NOT NULL,
    l_reason longtext NOT NULL,
    l_by integer NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_multi_moderations;

CREATE TABLE cms_f_multi_moderations (
    id integer unsigned auto_increment NOT NULL,
    mm_name longtext NOT NULL,
    mm_post_text longtext NOT NULL,
    mm_move_to integer NULL,
    mm_pin_state tinyint(1) NULL,
    mm_sink_state tinyint(1) NULL,
    mm_open_state tinyint(1) NULL,
    mm_forum_multi_code varchar(255) NOT NULL,
    mm_title_suffix varchar(255) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_multi_moderations (id, mm_name, mm_post_text, mm_move_to, mm_pin_state, mm_sink_state, mm_open_state, mm_forum_multi_code, mm_title_suffix) VALUES (1, 'Trash', '', 4, 0, 0, 0, '*', '');

ALTER TABLE cms10_f_multi_moderations ADD FULLTEXT mm_name (mm_name);

DROP TABLE IF EXISTS cms_f_password_history;

CREATE TABLE cms_f_password_history (
    id integer unsigned auto_increment NOT NULL,
    p_member_id integer NOT NULL,
    p_hash_salted varchar(255) NOT NULL,
    p_salt varchar(255) NOT NULL,
    p_time integer unsigned NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_f_password_history ADD INDEX p_member_id (p_member_id);

DROP TABLE IF EXISTS cms_f_poll_answers;

CREATE TABLE cms_f_poll_answers (
    id integer unsigned auto_increment NOT NULL,
    pa_poll_id integer NOT NULL,
    pa_answer varchar(255) NOT NULL,
    pa_cache_num_votes integer NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_poll_votes;

CREATE TABLE cms_f_poll_votes (
    id integer unsigned auto_increment NOT NULL,
    pv_poll_id integer NOT NULL,
    pv_member_id integer NOT NULL,
    pv_answer_id integer NOT NULL,
    pv_ip varchar(40) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_polls;

CREATE TABLE cms_f_polls (
    id integer unsigned auto_increment NOT NULL,
    po_question varchar(255) NOT NULL,
    po_cache_total_votes integer NOT NULL,
    po_is_private tinyint(1) NOT NULL,
    po_is_open tinyint(1) NOT NULL,
    po_minimum_selections integer NOT NULL,
    po_maximum_selections integer NOT NULL,
    po_requires_reply tinyint(1) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_post_templates;

CREATE TABLE cms_f_post_templates (
    id integer unsigned auto_increment NOT NULL,
    t_title varchar(255) NOT NULL,
    t_text longtext NOT NULL,
    t_forum_multi_code varchar(255) NOT NULL,
    t_use_default_forums tinyint(1) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_post_templates (id, t_title, t_text, t_forum_multi_code, t_use_default_forums) VALUES (1, 'Bug report', 'Version: ?
Support software environment (operating system, etc.):
?

Assigned to: ?
Severity: ?
Example URL: ?
Description:
?

Steps for reproduction:
?

', '', 0);
INSERT INTO cms_f_post_templates (id, t_title, t_text, t_forum_multi_code, t_use_default_forums) VALUES (2, 'Task', 'Assigned to: ?
Priority/Timescale: ?
Description:
?

', '', 0);
INSERT INTO cms_f_post_templates (id, t_title, t_text, t_forum_multi_code, t_use_default_forums) VALUES (3, 'Fault', 'Version: ?
Assigned to: ?
Severity/Timescale: ?
Description:
?

Steps for reproduction:
?

', '', 0);

DROP TABLE IF EXISTS cms_f_posts;

CREATE TABLE cms_f_posts (
    id integer unsigned auto_increment NOT NULL,
    p_title varchar(255) NOT NULL,
    p_post longtext NOT NULL,
    p_ip_address varchar(40) NOT NULL,
    p_time integer unsigned NOT NULL,
    p_poster integer NOT NULL,
    p_intended_solely_for integer NULL,
    p_poster_name_if_guest varchar(80) NOT NULL,
    p_validated tinyint(1) NOT NULL,
    p_topic_id integer NOT NULL,
    p_cache_forum_id integer NULL,
    p_last_edit_time integer unsigned NULL,
    p_last_edit_by integer NULL,
    p_is_emphasised tinyint(1) NOT NULL,
    p_skip_sig tinyint(1) NOT NULL,
    p_parent_id integer NULL,
    p_post__text_parsed longtext NOT NULL,
    p_post__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_posts (id, p_title, p_post, p_ip_address, p_time, p_poster, p_intended_solely_for, p_poster_name_if_guest, p_validated, p_topic_id, p_cache_forum_id, p_last_edit_time, p_last_edit_by, p_is_emphasised, p_skip_sig, p_parent_id, p_post__text_parsed, p_post__source_user) VALUES (1, 'Welcome to the forums', 'This is the inbuilt forum system (known as Conversr).

A forum system is a tool for communication between members; it consists of posts, organised into topics: each topic is a line of conversation.

Composr provides support for a number of different forum systems, and each forum handles authentication of members: Conversr is the built-in forum, which provides seamless integration between the main website, the forums, and the inbuilt member accounts system.', '127.0.0.1', 1524093340, 1, NULL, 'System', 1, 1, 7, NULL, NULL, 0, 0, NULL, 'return unserialize(\"a:5:{i:0;a:1:{i:0;a:7:{i:0;a:5:{i:0;s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_1\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}i:1;a:5:{i:0;s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_2\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}i:2;a:5:{i:0;s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_3\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}i:3;a:5:{i:0;s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_4\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}i:4;a:5:{i:0;s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_5\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}i:5;a:5:{i:0;s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_6\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}i:6;a:5:{i:0;s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_7\\\";i:1;a:0:{}i:2;i:1;i:3;s:0:\\\"\\\";i:4;s:0:\\\"\\\";}}}i:1;a:0:{}i:2;s:7:\\\"(mixed)\\\";i:3;N;i:4;a:7:{s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_1\\\";s:121:\\\"\\$tpl_funcs[\'string_attach_5ad7d19cf2c9e6.96101199_1\']=\\\"echo \\\\\\\"This is the inbuilt forum system (known as Conversr).\\\\\\\";\\\";\\n\\\";s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_2\\\";s:74:\\\"\\$tpl_funcs[\'string_attach_5ad7d19cf2c9e6.96101199_2\']=\\\"echo \\\\\\\"<br />\\\\\\\";\\\";\\n\\\";s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_3\\\";s:74:\\\"\\$tpl_funcs[\'string_attach_5ad7d19cf2c9e6.96101199_3\']=\\\"echo \\\\\\\"<br />\\\\\\\";\\\";\\n\\\";s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_4\\\";s:210:\\\"\\$tpl_funcs[\'string_attach_5ad7d19cf2c9e6.96101199_4\']=\\\"echo \\\\\\\"A forum system is a tool for communication between members; it consists of posts, organised into topics: each topic is a line of conversation.\\\\\\\";\\\";\\n\\\";s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_5\\\";s:74:\\\"\\$tpl_funcs[\'string_attach_5ad7d19cf2c9e6.96101199_5\']=\\\"echo \\\\\\\"<br />\\\\\\\";\\\";\\n\\\";s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_6\\\";s:74:\\\"\\$tpl_funcs[\'string_attach_5ad7d19cf2c9e6.96101199_6\']=\\\"echo \\\\\\\"<br />\\\\\\\";\\\";\\n\\\";s:39:\\\"string_attach_5ad7d19cf2c9e6.96101199_7\\\";s:329:\\\"\\$tpl_funcs[\'string_attach_5ad7d19cf2c9e6.96101199_7\']=\\\"echo \\\\\\\"Composr provides support for a number of different forum systems, and each forum handles authentication of members: Conversr is the built-in forum, which provides seamless integration between the main website, the forums, and the inbuilt member accounts system.\\\\\\\";\\\";\\n\\\";}}\");
', 1);

ALTER TABLE cms10_f_posts ADD FULLTEXT posts_search__combined (p_post,p_title);

ALTER TABLE cms10_f_posts ADD FULLTEXT p_post (p_post);

ALTER TABLE cms10_f_posts ADD FULLTEXT p_title (p_title);

ALTER TABLE cms10_f_posts ADD INDEX deletebyip (p_ip_address);

ALTER TABLE cms10_f_posts ADD INDEX find_pp (p_intended_solely_for);

ALTER TABLE cms10_f_posts ADD INDEX in_topic (p_topic_id,p_time,id);

ALTER TABLE cms10_f_posts ADD INDEX in_topic_change_order (p_topic_id,p_last_edit_time,p_time,id);

ALTER TABLE cms10_f_posts ADD INDEX postsinforum (p_cache_forum_id);

ALTER TABLE cms10_f_posts ADD INDEX posts_by (p_poster,p_time);

ALTER TABLE cms10_f_posts ADD INDEX posts_by_in_forum (p_poster,p_cache_forum_id);

ALTER TABLE cms10_f_posts ADD INDEX posts_by_in_topic (p_poster,p_topic_id);

ALTER TABLE cms10_f_posts ADD INDEX posts_since (p_time,p_cache_forum_id);

ALTER TABLE cms10_f_posts ADD INDEX post_order_time (p_time,id);

ALTER TABLE cms10_f_posts ADD INDEX p_last_edit_time (p_last_edit_time);

ALTER TABLE cms10_f_posts ADD INDEX p_validated (p_validated);

ALTER TABLE cms10_f_posts ADD INDEX search_join (p_post(250));

DROP TABLE IF EXISTS cms_f_read_logs;

CREATE TABLE cms_f_read_logs (
    l_member_id integer NOT NULL,
    l_topic_id integer NOT NULL,
    l_time integer unsigned NOT NULL,
    PRIMARY KEY (l_member_id, l_topic_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_f_read_logs ADD INDEX erase_old_read_logs (l_time);

DROP TABLE IF EXISTS cms_f_saved_warnings;

CREATE TABLE cms_f_saved_warnings (
    s_title varchar(255) NOT NULL,
    s_explanation longtext NOT NULL,
    s_message longtext NOT NULL,
    PRIMARY KEY (s_title)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_special_pt_access;

CREATE TABLE cms_f_special_pt_access (
    s_member_id integer NOT NULL,
    s_topic_id integer NOT NULL,
    PRIMARY KEY (s_member_id, s_topic_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_f_topics;

CREATE TABLE cms_f_topics (
    id integer unsigned auto_increment NOT NULL,
    t_pinned tinyint(1) NOT NULL,
    t_sunk tinyint(1) NOT NULL,
    t_cascading tinyint(1) NOT NULL,
    t_forum_id integer NULL,
    t_pt_from integer NULL,
    t_pt_to integer NULL,
    t_pt_from_category varchar(255) NOT NULL,
    t_pt_to_category varchar(255) NOT NULL,
    t_description varchar(255) NOT NULL,
    t_description_link varchar(255) NOT NULL,
    t_emoticon varchar(255) NOT NULL,
    t_num_views integer NOT NULL,
    t_validated tinyint(1) NOT NULL,
    t_is_open tinyint(1) NOT NULL,
    t_poll_id integer NULL,
    t_cache_first_post_id integer NULL,
    t_cache_first_time integer unsigned NULL,
    t_cache_first_title varchar(255) NOT NULL,
    t_cache_first_post longtext NOT NULL,
    t_cache_first_username varchar(80) NOT NULL,
    t_cache_first_member_id integer NULL,
    t_cache_last_post_id integer NULL,
    t_cache_last_time integer unsigned NULL,
    t_cache_last_title varchar(255) NOT NULL,
    t_cache_last_username varchar(80) NOT NULL,
    t_cache_last_member_id integer NULL,
    t_cache_num_posts integer NOT NULL,
    t_cache_first_post__text_parsed longtext NOT NULL,
    t_cache_first_post__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_f_topics (id, t_pinned, t_sunk, t_cascading, t_forum_id, t_pt_from, t_pt_to, t_pt_from_category, t_pt_to_category, t_description, t_description_link, t_emoticon, t_num_views, t_validated, t_is_open, t_poll_id, t_cache_first_post_id, t_cache_first_time, t_cache_first_title, t_cache_first_post, t_cache_first_username, t_cache_first_member_id, t_cache_last_post_id, t_cache_last_time, t_cache_last_title, t_cache_last_username, t_cache_last_member_id, t_cache_num_posts, t_cache_first_post__text_parsed, t_cache_first_post__source_user) VALUES (1, 0, 0, 0, 7, NULL, NULL, '', '', '', '', '', 0, 1, 1, NULL, 1, 1524093340, 'Welcome to the forums', '', 'System', 1, 1, 1524093340, 'Welcome to the forums', 'System', 1, 1, '', 1);

ALTER TABLE cms10_f_topics ADD FULLTEXT t_cache_first_post (t_cache_first_post);

ALTER TABLE cms10_f_topics ADD FULLTEXT t_description (t_description);

ALTER TABLE cms10_f_topics ADD INDEX descriptionsearch (t_description(250));

ALTER TABLE cms10_f_topics ADD INDEX forumlayer (t_cache_first_title(250));

ALTER TABLE cms10_f_topics ADD INDEX in_forum (t_forum_id);

ALTER TABLE cms10_f_topics ADD INDEX ownedtopics (t_cache_first_member_id);

ALTER TABLE cms10_f_topics ADD INDEX topic_order (t_cascading,t_pinned,t_cache_last_time);

ALTER TABLE cms10_f_topics ADD INDEX topic_order_2 (t_forum_id,t_cascading,t_pinned,t_sunk,t_cache_last_time);

ALTER TABLE cms10_f_topics ADD INDEX topic_order_3 (t_forum_id,t_cascading,t_pinned,t_cache_last_time);

ALTER TABLE cms10_f_topics ADD INDEX topic_order_time (t_cache_last_time);

ALTER TABLE cms10_f_topics ADD INDEX topic_order_time_2 (t_cache_first_time);

ALTER TABLE cms10_f_topics ADD INDEX t_cache_first_post_id (t_cache_first_post_id);

ALTER TABLE cms10_f_topics ADD INDEX t_cache_last_member_id (t_cache_last_member_id);

ALTER TABLE cms10_f_topics ADD INDEX t_cache_last_post_id (t_cache_last_post_id);

ALTER TABLE cms10_f_topics ADD INDEX t_cache_num_posts (t_cache_num_posts);

ALTER TABLE cms10_f_topics ADD INDEX t_cascading (t_cascading);

ALTER TABLE cms10_f_topics ADD INDEX t_cascading_or_forum (t_cascading,t_forum_id);

ALTER TABLE cms10_f_topics ADD INDEX t_num_views (t_num_views);

ALTER TABLE cms10_f_topics ADD INDEX t_pt_from (t_pt_from);

ALTER TABLE cms10_f_topics ADD INDEX t_pt_to (t_pt_to);

ALTER TABLE cms10_f_topics ADD INDEX t_validated (t_validated);

ALTER TABLE cms10_f_topics ADD INDEX unread_forums (t_forum_id,t_cache_last_time);

DROP TABLE IF EXISTS cms_f_warnings;

CREATE TABLE cms_f_warnings (
    id integer unsigned auto_increment NOT NULL,
    w_member_id integer NOT NULL,
    w_time integer unsigned NOT NULL,
    w_explanation longtext NOT NULL,
    w_by integer NOT NULL,
    w_is_warning tinyint(1) NOT NULL,
    p_silence_from_topic integer NULL,
    p_silence_from_forum integer NULL,
    p_probation integer NOT NULL,
    p_banned_ip varchar(40) NOT NULL,
    p_charged_points integer NOT NULL,
    p_banned_member tinyint(1) NOT NULL,
    p_changed_usergroup_from integer NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_f_warnings ADD INDEX warningsmemberid (w_member_id);

DROP TABLE IF EXISTS cms_failedlogins;

CREATE TABLE cms_failedlogins (
    id integer unsigned auto_increment NOT NULL,
    failed_account varchar(80) NOT NULL,
    date_and_time integer unsigned NOT NULL,
    ip varchar(40) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_failedlogins ADD INDEX failedlogins_by_ip (ip);

DROP TABLE IF EXISTS cms_filedump;

CREATE TABLE cms_filedump (
    id integer unsigned auto_increment NOT NULL,
    name varchar(80) NOT NULL,
    path varchar(255) BINARY NOT NULL,
    description longtext NOT NULL,
    the_member integer NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_filedump ADD FULLTEXT description (description);

DROP TABLE IF EXISTS cms_group_category_access;

CREATE TABLE cms_group_category_access (
    module_the_name varchar(80) NOT NULL,
    category_name varchar(80) NOT NULL,
    group_id integer NOT NULL,
    PRIMARY KEY (module_the_name, category_name, group_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 1);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 2);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 3);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 4);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 5);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 6);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 7);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 8);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 9);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '1', 10);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 1);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 2);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 3);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 4);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 5);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 6);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 7);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 8);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 9);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '2', 10);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '3', 2);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '3', 3);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '4', 2);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '4', 3);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 1);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 2);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 3);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 4);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 5);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 6);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 7);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 8);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 9);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '5', 10);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '6', 2);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '6', 3);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '7', 2);
INSERT INTO cms_group_category_access (module_the_name, category_name, group_id) VALUES ('forums', '7', 3);

DROP TABLE IF EXISTS cms_group_page_access;

CREATE TABLE cms_group_page_access (
    page_name varchar(80) NOT NULL,
    zone_name varchar(80) NOT NULL,
    group_id integer NOT NULL,
    PRIMARY KEY (page_name, zone_name, group_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 1);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 2);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 3);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 4);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 5);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 6);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 7);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 8);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 9);
INSERT INTO cms_group_page_access (page_name, zone_name, group_id) VALUES ('filedump', 'cms', 10);

ALTER TABLE cms10_group_page_access ADD INDEX group_id (group_id);

DROP TABLE IF EXISTS cms_group_privileges;

CREATE TABLE cms_group_privileges (
    group_id integer NOT NULL,
    privilege varchar(80) NOT NULL,
    the_page varchar(80) NOT NULL,
    module_the_name varchar(80) NOT NULL,
    category_name varchar(80) NOT NULL,
    the_value tinyint(1) NOT NULL,
    PRIMARY KEY (group_id, privilege, the_page, module_the_name, category_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'submit_lowrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'submit_midrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_lowrange_content', '', 'forums', '1', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_midrange_content', '', 'forums', '1', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_lowrange_content', '', 'forums', '1', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_midrange_content', '', 'forums', '1', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'submit_lowrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'submit_midrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'submit_lowrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'submit_midrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'submit_lowrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'submit_midrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'submit_lowrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'submit_midrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'submit_lowrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'submit_midrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'submit_lowrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'submit_midrange_content', '', 'forums', '1', 0);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'bypass_validation_lowrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'bypass_validation_midrange_content', '', 'forums', '2', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_lowrange_content', '', 'forums', '3', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_midrange_content', '', 'forums', '3', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_lowrange_content', '', 'forums', '3', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_midrange_content', '', 'forums', '3', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_lowrange_content', '', 'forums', '4', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_midrange_content', '', 'forums', '4', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_lowrange_content', '', 'forums', '4', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_midrange_content', '', 'forums', '4', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'bypass_validation_lowrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'bypass_validation_midrange_content', '', 'forums', '5', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_lowrange_content', '', 'forums', '6', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_midrange_content', '', 'forums', '6', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_lowrange_content', '', 'forums', '6', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_midrange_content', '', 'forums', '6', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_lowrange_content', '', 'forums', '7', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'bypass_validation_midrange_content', '', 'forums', '7', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_lowrange_content', '', 'forums', '7', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_validation_midrange_content', '', 'forums', '7', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'run_multi_moderations', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'use_pt', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'edit_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'may_unblind_own_poll', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'may_report_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'view_member_photos', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'use_quick_reply', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'view_profiles', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'own_avatars', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'double_post', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'delete_account', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'rename_self', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'use_special_emoticons', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'view_any_profile_field', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'disable_lost_passwords', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'close_own_topics', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'edit_own_polls', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'see_warnings', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'see_ip', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'may_choose_custom_title', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'view_poll_results_before_voting', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'moderate_private_topic', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'member_maintenance', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'probate_members', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'warn_members', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'control_usergroups', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'multi_delete_topics', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'show_user_browsing', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'see_hidden_groups', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'pt_anyone', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'delete_private_topic_posts', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'exceed_post_edit_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'exceed_post_delete_time_limit', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_required_cpfs', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_required_cpfs_if_already_empty', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_email_address', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_email_address_if_already_empty', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_dob', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'bypass_dob_if_already_empty', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'edit_meta_fields', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'edit_meta_fields', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'perform_webstandards_check_by_default', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'perform_webstandards_check_by_default', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'set_own_author_profile', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'set_own_author_profile', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'upload_anything_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'upload_anything_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (1, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (4, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (5, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (6, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (7, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (8, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (9, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (10, 'upload_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (2, 'delete_anything_filedump', '', '', '', 1);
INSERT INTO cms_group_privileges (group_id, privilege, the_page, module_the_name, category_name, the_value) VALUES (3, 'delete_anything_filedump', '', '', '', 1);

ALTER TABLE cms10_group_privileges ADD INDEX group_id (group_id);

DROP TABLE IF EXISTS cms_group_zone_access;

CREATE TABLE cms_group_zone_access (
    zone_name varchar(80) NOT NULL,
    group_id integer NOT NULL,
    PRIMARY KEY (zone_name, group_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_group_zone_access ADD INDEX group_id (group_id);

DROP TABLE IF EXISTS cms_https_pages;

CREATE TABLE cms_https_pages (
    https_page_name varchar(80) NOT NULL,
    PRIMARY KEY (https_page_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_incoming_uploads;

CREATE TABLE cms_incoming_uploads (
    id integer unsigned auto_increment NOT NULL,
    i_submitter integer NOT NULL,
    i_date_and_time integer unsigned NOT NULL,
    i_orig_filename varchar(255) BINARY NOT NULL,
    i_save_url varchar(255) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_link_tracker;

CREATE TABLE cms_link_tracker (
    id integer unsigned auto_increment NOT NULL,
    c_date_and_time integer unsigned NOT NULL,
    c_member_id integer NOT NULL,
    c_ip_address varchar(40) NOT NULL,
    c_url varchar(255) BINARY NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_link_tracker ADD INDEX c_url (c_url(250));

DROP TABLE IF EXISTS cms_logged_mail_messages;

CREATE TABLE cms_logged_mail_messages (
    id integer unsigned auto_increment NOT NULL,
    m_subject longtext NOT NULL,
    m_message longtext NOT NULL,
    m_to_email longtext NOT NULL,
    m_extra_cc_addresses longtext NOT NULL,
    m_extra_bcc_addresses longtext NOT NULL,
    m_join_time integer unsigned NULL,
    m_to_name longtext NOT NULL,
    m_from_email varchar(255) NOT NULL,
    m_from_name varchar(255) NOT NULL,
    m_priority tinyint NOT NULL,
    m_attachments longtext NOT NULL,
    m_no_cc tinyint(1) NOT NULL,
    m_as integer NOT NULL,
    m_as_admin tinyint(1) NOT NULL,
    m_in_html tinyint(1) NOT NULL,
    m_date_and_time integer unsigned NOT NULL,
    m_member_id integer NOT NULL,
    m_url longtext NOT NULL,
    m_queued tinyint(1) NOT NULL,
    m_template varchar(80) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_logged_mail_messages ADD INDEX combo (m_date_and_time,m_queued);

ALTER TABLE cms10_logged_mail_messages ADD INDEX queued (m_queued);

ALTER TABLE cms10_logged_mail_messages ADD INDEX recentmessages (m_date_and_time);

DROP TABLE IF EXISTS cms_match_key_messages;

CREATE TABLE cms_match_key_messages (
    id integer unsigned auto_increment NOT NULL,
    k_message longtext NOT NULL,
    k_match_key varchar(255) NOT NULL,
    k_message__text_parsed longtext NOT NULL,
    k_message__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_match_key_messages ADD FULLTEXT k_message (k_message);

DROP TABLE IF EXISTS cms_member_category_access;

CREATE TABLE cms_member_category_access (
    module_the_name varchar(80) NOT NULL,
    category_name varchar(80) NOT NULL,
    member_id integer NOT NULL,
    active_until integer unsigned NULL,
    PRIMARY KEY (module_the_name, category_name, member_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_member_category_access ADD INDEX mcamember_id (member_id);

ALTER TABLE cms10_member_category_access ADD INDEX mcaname (module_the_name,category_name);

DROP TABLE IF EXISTS cms_member_page_access;

CREATE TABLE cms_member_page_access (
    page_name varchar(80) NOT NULL,
    zone_name varchar(80) NOT NULL,
    member_id integer NOT NULL,
    active_until integer unsigned NULL,
    PRIMARY KEY (page_name, zone_name, member_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_member_page_access ADD INDEX mzamember_id (member_id);

ALTER TABLE cms10_member_page_access ADD INDEX mzaname (page_name,zone_name);

DROP TABLE IF EXISTS cms_member_privileges;

CREATE TABLE cms_member_privileges (
    member_id integer NOT NULL,
    privilege varchar(80) NOT NULL,
    the_page varchar(80) NOT NULL,
    module_the_name varchar(80) NOT NULL,
    category_name varchar(80) NOT NULL,
    the_value tinyint(1) NOT NULL,
    active_until integer unsigned NULL,
    PRIMARY KEY (member_id, privilege, the_page, module_the_name, category_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_member_privileges ADD INDEX member_privileges_member (member_id);

ALTER TABLE cms10_member_privileges ADD INDEX member_privileges_name (privilege,the_page,module_the_name,category_name);

DROP TABLE IF EXISTS cms_member_tracking;

CREATE TABLE cms_member_tracking (
    mt_member_id integer NOT NULL,
    mt_cache_username varchar(80) NOT NULL,
    mt_time integer unsigned NOT NULL,
    mt_page varchar(80) NOT NULL,
    mt_type varchar(80) NOT NULL,
    mt_id varchar(80) NOT NULL,
    PRIMARY KEY (mt_member_id, mt_time, mt_page, mt_type, mt_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_member_tracking ADD INDEX mt_id (mt_page,mt_id,mt_type);

ALTER TABLE cms10_member_tracking ADD INDEX mt_page (mt_page);

ALTER TABLE cms10_member_tracking ADD INDEX mt_time (mt_time);

DROP TABLE IF EXISTS cms_member_zone_access;

CREATE TABLE cms_member_zone_access (
    zone_name varchar(80) NOT NULL,
    member_id integer NOT NULL,
    active_until integer unsigned NULL,
    PRIMARY KEY (zone_name, member_id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_member_zone_access ADD INDEX mzamember_id (member_id);

ALTER TABLE cms10_member_zone_access ADD INDEX mzazone_name (zone_name);

DROP TABLE IF EXISTS cms_menu_items;

CREATE TABLE cms_menu_items (
    id integer unsigned auto_increment NOT NULL,
    i_menu varchar(80) NOT NULL,
    i_order integer NOT NULL,
    i_parent integer NULL,
    i_caption longtext NOT NULL,
    i_caption_long longtext NOT NULL,
    i_url varchar(255) NOT NULL,
    i_check_permissions tinyint(1) NOT NULL,
    i_expanded tinyint(1) NOT NULL,
    i_new_window tinyint(1) NOT NULL,
    i_include_sitemap tinyint NOT NULL,
    i_page_only varchar(80) NOT NULL,
    i_theme_img_code varchar(80) NOT NULL,
    i_caption__text_parsed longtext NOT NULL,
    i_caption__source_user integer DEFAULT 1 NOT NULL,
    i_caption_long__text_parsed longtext NOT NULL,
    i_caption_long__source_user integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_menu_items ADD FULLTEXT i_caption (i_caption);

ALTER TABLE cms10_menu_items ADD FULLTEXT i_caption_long (i_caption_long);

ALTER TABLE cms10_menu_items ADD INDEX menu_extraction (i_menu);

DROP TABLE IF EXISTS cms_messages_to_render;

CREATE TABLE cms_messages_to_render (
    id integer unsigned auto_increment NOT NULL,
    r_session_id varchar(80) NOT NULL,
    r_message longtext NOT NULL,
    r_type varchar(80) NOT NULL,
    r_time integer unsigned NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_messages_to_render ADD INDEX forsession (r_session_id);

DROP TABLE IF EXISTS cms_modules;

CREATE TABLE cms_modules (
    module_the_name varchar(80) NOT NULL,
    module_author varchar(80) NOT NULL,
    module_organisation varchar(80) NOT NULL,
    module_hacked_by varchar(80) NOT NULL,
    module_hack_version integer NULL,
    module_version integer NOT NULL,
    PRIMARY KEY (module_the_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('admin_permissions', 'Chris Graham', 'ocProducts', '', NULL, 9);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('admin_version', 'Chris Graham', 'ocProducts', '', NULL, 17);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('authors', 'Chris Graham', 'ocProducts', '', NULL, 4);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('forumview', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('topics', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('topicview', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('vforums', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_authors', 'Chris Graham', 'ocProducts', '', NULL, 3);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_banners', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_blogs', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_calendar', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_catalogues', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_chat', 'Philip Withnall', 'ocProducts', '', NULL, 3);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_cns_groups', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_comcode_pages', 'Chris Graham', 'ocProducts', '', NULL, 4);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_downloads', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_galleries', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_news', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_polls', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_quiz', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('cms_wiki', 'Chris Graham', 'ocProducts', '', NULL, 4);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('filedump', 'Chris Graham', 'ocProducts', '', NULL, 4);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('forums', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('join', 'Chris Graham', 'ocProducts', '', NULL, 2);
INSERT INTO cms_modules (module_the_name, module_author, module_organisation, module_hacked_by, module_hack_version, module_version) VALUES ('login', 'Chris Graham', 'ocProducts', '', NULL, 3);

DROP TABLE IF EXISTS cms_notifications_enabled;

CREATE TABLE cms_notifications_enabled (
    id integer unsigned auto_increment NOT NULL,
    l_member_id integer NOT NULL,
    l_notification_code varchar(80) NOT NULL,
    l_code_category varchar(255) NOT NULL,
    l_setting integer NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_notifications_enabled ADD INDEX l_code_category (l_code_category(250));

ALTER TABLE cms10_notifications_enabled ADD INDEX l_member_id (l_member_id,l_notification_code);

ALTER TABLE cms10_notifications_enabled ADD INDEX l_notification_code (l_notification_code);

DROP TABLE IF EXISTS cms_post_tokens;

CREATE TABLE cms_post_tokens (
    token varchar(80) NOT NULL,
    generation_time integer unsigned NOT NULL,
    member_id integer NOT NULL,
    session_id varchar(80) NOT NULL,
    ip_address varchar(40) NOT NULL,
    usage_tally integer NOT NULL,
    PRIMARY KEY (token)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_post_tokens ADD INDEX generation_time (generation_time);

DROP TABLE IF EXISTS cms_privilege_list;

CREATE TABLE cms_privilege_list (
    p_section varchar(80) NOT NULL,
    the_name varchar(80) NOT NULL,
    the_default tinyint(1) NOT NULL,
    PRIMARY KEY (the_name, the_default)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('_COMCODE', 'allow_html', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('_COMCODE', 'comcode_dangerous', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('_COMCODE', 'comcode_nuisance', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('_COMCODE', 'use_very_dangerous_comcode', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('STAFF_ACTIONS', 'access_closed_site', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('STAFF_ACTIONS', 'bypass_bandwidth_restriction', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('STAFF_ACTIONS', 'see_php_errors', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('STAFF_ACTIONS', 'see_stack_dump', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('STAFF_ACTIONS', 'view_profiling_modes', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('STAFF_ACTIONS', 'access_overrun_site', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'feature', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'bypass_validation_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'bypass_validation_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_lowrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_own_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_own_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_lowrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_own_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_own_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_own_lowrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'can_submit_to_others_categories', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'search_engine_links', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'submit_cat_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'submit_cat_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'submit_cat_lowrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_cat_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_cat_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_cat_lowrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_cat_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_cat_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_cat_lowrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_own_cat_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_own_cat_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_own_cat_lowrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_own_cat_highrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_own_cat_midrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'delete_own_cat_lowrange_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'mass_import', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'scheduled_publication_times', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'mass_delete_from_ip', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'exceed_filesize_limit', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'draw_to_server', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'open_virtual_roots', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'sees_javascript_error_alerts', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'see_software_docs', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'see_unvalidated', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'may_enable_staff_notifications', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'bypass_flood_control', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'remove_page_split', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'bypass_wordfilter', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'perform_keyword_check', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'have_personal_category', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('STAFF_ACTIONS', 'assume_any_member', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_own_lowrange_content', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'submit_highrange_content', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'submit_midrange_content', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'submit_lowrange_content', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'bypass_validation_lowrange_content', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('_FEEDBACK', 'rate', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('_FEEDBACK', 'comment', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('VOTE', 'vote_in_polls', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('GENERAL_SETTINGS', 'jump_to_unvalidated', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('_COMCODE', 'reuse_others_attachments', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'unfiltered_input', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'run_multi_moderations', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'use_pt', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'edit_private_topic_posts', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'may_unblind_own_poll', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'may_report_post', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'view_member_photos', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'use_quick_reply', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'view_profiles', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'own_avatars', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'double_post', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'delete_account', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'rename_self', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'use_special_emoticons', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'view_any_profile_field', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'disable_lost_passwords', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'close_own_topics', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'edit_own_polls', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'see_warnings', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'see_ip', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'may_choose_custom_title', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'view_other_pt', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'view_poll_results_before_voting', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'moderate_private_topic', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'member_maintenance', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'probate_members', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'warn_members', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'control_usergroups', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'multi_delete_topics', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'show_user_browsing', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'see_hidden_groups', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'pt_anyone', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'delete_private_topic_posts', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'exceed_post_edit_time_limit', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'exceed_post_delete_time_limit', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'bypass_required_cpfs', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'bypass_required_cpfs_if_already_empty', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'bypass_email_address', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'bypass_email_address_if_already_empty', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'bypass_dob', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FORUMS_AND_MEMBERS', 'bypass_dob_if_already_empty', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'edit_meta_fields', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'perform_webstandards_check_by_default', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'view_private_content', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('SUBMISSION', 'set_own_author_profile', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FILEDUMP', 'upload_anything_filedump', 0);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FILEDUMP', 'upload_filedump', 1);
INSERT INTO cms_privilege_list (p_section, the_name, the_default) VALUES ('FILEDUMP', 'delete_anything_filedump', 0);

DROP TABLE IF EXISTS cms_rating;

CREATE TABLE cms_rating (
    id integer unsigned auto_increment NOT NULL,
    rating_for_type varchar(80) NOT NULL,
    rating_for_id varchar(80) NOT NULL,
    rating_member integer NOT NULL,
    rating_ip varchar(40) NOT NULL,
    rating_time integer unsigned NOT NULL,
    rating tinyint NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_rating ADD INDEX alt_key (rating_for_type,rating_for_id);

ALTER TABLE cms10_rating ADD INDEX rating_for_id (rating_for_id);

DROP TABLE IF EXISTS cms_review_supplement;

CREATE TABLE cms_review_supplement (
    r_post_id integer NOT NULL,
    r_rating_type varchar(80) NOT NULL,
    r_rating tinyint NOT NULL,
    r_topic_id integer NOT NULL,
    r_rating_for_id varchar(80) NOT NULL,
    r_rating_for_type varchar(80) NOT NULL,
    PRIMARY KEY (r_post_id, r_rating_type)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_review_supplement ADD INDEX rating_for_id (r_rating_for_id);

DROP TABLE IF EXISTS cms_seo_meta;

CREATE TABLE cms_seo_meta (
    id integer unsigned auto_increment NOT NULL,
    meta_for_type varchar(80) NOT NULL,
    meta_for_id varchar(80) NOT NULL,
    meta_description longtext NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_seo_meta ADD FULLTEXT meta_description (meta_description);

ALTER TABLE cms10_seo_meta ADD INDEX alt_key (meta_for_type,meta_for_id);

ALTER TABLE cms10_seo_meta ADD INDEX ftjoin_dmeta_description (meta_description(250));

DROP TABLE IF EXISTS cms_seo_meta_keywords;

CREATE TABLE cms_seo_meta_keywords (
    id integer unsigned auto_increment NOT NULL,
    meta_for_type varchar(80) NOT NULL,
    meta_for_id varchar(80) NOT NULL,
    meta_keyword longtext NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_seo_meta_keywords ADD FULLTEXT meta_keyword (meta_keyword);

ALTER TABLE cms10_seo_meta_keywords ADD INDEX ftjoin_dmeta_keywords (meta_keyword(250));

ALTER TABLE cms10_seo_meta_keywords ADD INDEX keywords_alt_key (meta_for_type,meta_for_id);

DROP TABLE IF EXISTS cms_sessions;

CREATE TABLE cms_sessions (
    the_session varchar(80) NOT NULL,
    last_activity integer unsigned NOT NULL,
    member_id integer NOT NULL,
    ip varchar(40) NOT NULL,
    session_confirmed tinyint(1) NOT NULL,
    session_invisible tinyint(1) NOT NULL,
    cache_username varchar(255) NOT NULL,
    the_zone varchar(80) NOT NULL,
    the_page varchar(80) NOT NULL,
    the_type varchar(80) NOT NULL,
    the_id varchar(80) NOT NULL,
    the_title varchar(255) NOT NULL,
    PRIMARY KEY (the_session)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_sessions ADD INDEX delete_old (last_activity);

ALTER TABLE cms10_sessions ADD INDEX member_id (member_id);

ALTER TABLE cms10_sessions ADD INDEX userat (the_zone,the_page,the_id);

DROP TABLE IF EXISTS cms_sitemap_cache;

CREATE TABLE cms_sitemap_cache (
    page_link varchar(255) NOT NULL,
    set_number integer NOT NULL,
    add_date integer unsigned NULL,
    edit_date integer unsigned NULL,
    last_updated integer unsigned NOT NULL,
    is_deleted tinyint(1) NOT NULL,
    priority real NOT NULL,
    refreshfreq varchar(80) NOT NULL,
    guest_access tinyint(1) NOT NULL,
    PRIMARY KEY (page_link)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_sitemap_cache ADD INDEX is_deleted (is_deleted);

ALTER TABLE cms10_sitemap_cache ADD INDEX last_updated (last_updated);

ALTER TABLE cms10_sitemap_cache ADD INDEX set_number (set_number,last_updated);

DROP TABLE IF EXISTS cms_task_queue;

CREATE TABLE cms_task_queue (
    id integer unsigned auto_increment NOT NULL,
    t_title varchar(255) NOT NULL,
    t_hook varchar(80) NOT NULL,
    t_args longtext NOT NULL,
    t_member_id integer NOT NULL,
    t_secure_ref varchar(80) NOT NULL,
    t_send_notification tinyint(1) NOT NULL,
    t_locked tinyint(1) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_temp_block_permissions;

CREATE TABLE cms_temp_block_permissions (
    id integer unsigned auto_increment NOT NULL,
    p_session_id varchar(80) NOT NULL,
    p_block_constraints longtext NOT NULL,
    p_time integer unsigned NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_trackbacks;

CREATE TABLE cms_trackbacks (
    id integer unsigned auto_increment NOT NULL,
    trackback_for_type varchar(80) NOT NULL,
    trackback_for_id varchar(80) NOT NULL,
    trackback_ip varchar(40) NOT NULL,
    trackback_time integer unsigned NOT NULL,
    trackback_url varchar(255) NOT NULL,
    trackback_title varchar(255) NOT NULL,
    trackback_excerpt longtext NOT NULL,
    trackback_name varchar(255) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_trackbacks ADD INDEX trackback_for_id (trackback_for_id);

ALTER TABLE cms10_trackbacks ADD INDEX trackback_for_type (trackback_for_type);

ALTER TABLE cms10_trackbacks ADD INDEX trackback_time (trackback_time);

DROP TABLE IF EXISTS cms_translate;

CREATE TABLE cms_translate (
    id integer unsigned auto_increment NOT NULL,
    language varchar(5) NOT NULL,
    importance_level tinyint NOT NULL,
    text_original longtext NOT NULL,
    text_parsed longtext NOT NULL,
    broken tinyint(1) NOT NULL,
    source_user integer NOT NULL,
    PRIMARY KEY (id, language)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_translate ADD FULLTEXT tsearch (text_original);

ALTER TABLE cms10_translate ADD INDEX decache (text_parsed(2));

ALTER TABLE cms10_translate ADD INDEX equiv_lang (text_original(4));

ALTER TABLE cms10_translate ADD INDEX importance_level (importance_level);

DROP TABLE IF EXISTS cms_tutorial_links;

CREATE TABLE cms_tutorial_links (
    the_name varchar(80) NOT NULL,
    the_value longtext NOT NULL,
    PRIMARY KEY (the_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_unbannable_ip;

CREATE TABLE cms_unbannable_ip (
    ip varchar(40) NOT NULL,
    note varchar(255) NOT NULL,
    PRIMARY KEY (ip)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_url_id_monikers;

CREATE TABLE cms_url_id_monikers (
    id integer unsigned auto_increment NOT NULL,
    m_resource_page varchar(80) NOT NULL,
    m_resource_type varchar(80) NOT NULL,
    m_resource_id varchar(80) NOT NULL,
    m_moniker varchar(255) NOT NULL,
    m_moniker_reversed varchar(255) NOT NULL,
    m_deprecated tinyint(1) NOT NULL,
    m_manually_chosen tinyint(1) NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_url_id_monikers ADD INDEX uim_moniker (m_moniker(250));

ALTER TABLE cms10_url_id_monikers ADD INDEX uim_monrev (m_moniker_reversed(250));

ALTER TABLE cms10_url_id_monikers ADD INDEX uim_page_link (m_resource_page,m_resource_type,m_resource_id);

DROP TABLE IF EXISTS cms_url_title_cache;

CREATE TABLE cms_url_title_cache (
    id integer unsigned auto_increment NOT NULL,
    t_url varchar(255) BINARY NOT NULL,
    t_title varchar(255) NOT NULL,
    t_meta_title longtext NOT NULL,
    t_keywords longtext NOT NULL,
    t_description longtext NOT NULL,
    t_image_url varchar(255) BINARY NOT NULL,
    t_mime_type varchar(80) NOT NULL,
    t_json_discovery varchar(255) BINARY NOT NULL,
    t_xml_discovery varchar(255) BINARY NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_url_title_cache ADD INDEX t_url (t_url(250));

DROP TABLE IF EXISTS cms_urls_checked;

CREATE TABLE cms_urls_checked (
    id integer unsigned auto_increment NOT NULL,
    url longtext NOT NULL,
    url_exists tinyint(1) NOT NULL,
    url_check_time integer unsigned NOT NULL,
    PRIMARY KEY (id)
) CHARACTER SET=utf8mb4 engine=MyISAM;

ALTER TABLE cms10_urls_checked ADD INDEX url (url(200));

DROP TABLE IF EXISTS cms_values;

CREATE TABLE cms_values (
    the_name varchar(80) NOT NULL,
    the_value varchar(255) NOT NULL,
    date_and_time integer unsigned NOT NULL,
    PRIMARY KEY (the_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_values (the_name, the_value, date_and_time) VALUES ('cns_topic_count', '1', 1524093340);
INSERT INTO cms_values (the_name, the_value, date_and_time) VALUES ('cns_member_count', '1', 1524093340);
INSERT INTO cms_values (the_name, the_value, date_and_time) VALUES ('cns_post_count', '1', 1524093341);
INSERT INTO cms_values (the_name, the_value, date_and_time) VALUES ('version', '10.00', 1524093346);
INSERT INTO cms_values (the_name, the_value, date_and_time) VALUES ('cns_version', '10.00', 1524093346);

ALTER TABLE cms10_values ADD INDEX date_and_time (date_and_time);

DROP TABLE IF EXISTS cms_values_elective;

CREATE TABLE cms_values_elective (
    the_name varchar(80) NOT NULL,
    the_value longtext NOT NULL,
    date_and_time integer unsigned NOT NULL,
    PRIMARY KEY (the_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_values_elective (the_name, the_value, date_and_time) VALUES ('call_home', '0', 1524093346);

DROP TABLE IF EXISTS cms_webstandards_checked_once;

CREATE TABLE cms_webstandards_checked_once (
    hash varchar(255) NOT NULL,
    PRIMARY KEY (hash)
) CHARACTER SET=utf8mb4 engine=MyISAM;

DROP TABLE IF EXISTS cms_zones;

CREATE TABLE cms_zones (
    zone_name varchar(80) NOT NULL,
    zone_title longtext NOT NULL,
    zone_default_page varchar(80) NOT NULL,
    zone_header_text longtext NOT NULL,
    zone_theme varchar(80) NOT NULL,
    zone_require_session tinyint(1) NOT NULL,
    PRIMARY KEY (zone_name)
) CHARACTER SET=utf8mb4 engine=MyISAM;

INSERT INTO cms_zones (zone_name, zone_title, zone_default_page, zone_header_text, zone_theme, zone_require_session) VALUES ('', 'Welcome', 'start', '', '-1', 0);
INSERT INTO cms_zones (zone_name, zone_title, zone_default_page, zone_header_text, zone_theme, zone_require_session) VALUES ('adminzone', 'Admin Zone', 'start', 'Admin Zone', 'admin', 1);
INSERT INTO cms_zones (zone_name, zone_title, zone_default_page, zone_header_text, zone_theme, zone_require_session) VALUES ('collaboration', 'Collaboration Zone', 'start', 'Collaboration Zone', '-1', 0);
INSERT INTO cms_zones (zone_name, zone_title, zone_default_page, zone_header_text, zone_theme, zone_require_session) VALUES ('site', 'Site', 'start', '', '-1', 0);
INSERT INTO cms_zones (zone_name, zone_title, zone_default_page, zone_header_text, zone_theme, zone_require_session) VALUES ('cms', 'Content Management', 'cms', 'Content Management', 'admin', 1);
INSERT INTO cms_zones (zone_name, zone_title, zone_default_page, zone_header_text, zone_theme, zone_require_session) VALUES ('docs', 'Tutorials', 'tutorials', '', '-1', 0);
INSERT INTO cms_zones (zone_name, zone_title, zone_default_page, zone_header_text, zone_theme, zone_require_session) VALUES ('forum', 'Forums', 'forumview', 'Forum', '-1', 0);

ALTER TABLE cms10_zones ADD FULLTEXT zone_header_text (zone_header_text);

ALTER TABLE cms10_zones ADD FULLTEXT zone_title (zone_title);

