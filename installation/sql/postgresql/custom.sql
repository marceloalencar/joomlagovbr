INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'blankcomponent', 'component', 'com_blankcomponent', '', '', 1, 1, 0, 0, 0, '', '{}', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'component'
    AND "element" = 'com_blankcomponent'
    AND "client_id" = 1
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'plg_sampledata_egov', 'plugin', 'egov', '', 'sampledata', 0, 1, 1, 0, 0, '', '', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'plugin'
    AND "element" = 'egov'
    AND "folder" = 'sampledata'
    AND "client_id" = 0
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'mod_chamadas', 'module', 'mod_chamadas', '', '', 0, 1, 1, 0, 0, '', '', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'module'
    AND "element" = 'mod_chamadas'
    AND "client_id" = 0
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'mod_container', 'module', 'mod_container', '', '', 0, 1, 1, 0, 0, '', '', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'module'
    AND "element" = 'mod_container'
    AND "client_id" = 0
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'mod_htmlcustom', 'module', 'mod_htmlcustom', '', '', 0, 1, 1, 0, 0, '', '', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'module'
    AND "element" = 'mod_htmlcustom'
    AND "client_id" = 0
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'mod_redes_sociais', 'module', 'mod_redes_sociais', '', '', 0, 1, 1, 0, 0, '', '', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'module'
    AND "element" = 'mod_redes_sociais'
    AND "client_id" = 0
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'mod_videosdestaque', 'module', 'mod_videosdestaque', '', '', 0, 1, 1, 0, 0, '', '', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'module'
    AND "element" = 'mod_videosdestaque'
    AND "client_id" = 0
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'padraogoverno01', 'template', 'padraogoverno01', '', '', 0, 1, 1, 0, 0, '', '{"cor":"azul","font_style_url":"{LOCALFONT}","icon_style":"bitmap-portal-brasil","denominacao":"Denomina\u00e7\u00e3o do \u00f3rg\u00e3o","nome_principal":"Nome principal","subordinacao":"subordina\u00e7\u00e3o","classe_nome_principal":"","emblema":"","rodape_acesso_informacao":1,"rodape_logo_brasil":1,"mensagem_final_ferramenta":"","clear_default_javascript":1,"local_mainscript":"footer","anexar_js_barra2014":"1","endereco_js_barra2014":"https:\/\/barra.brasil.gov.br\/barra.js?cor=verde","google_analytics_id":"","google_analytics_domain_name":"","google_analytics_allow_linker":0,"allow_set_color":"0","meta_generator":""}', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'template'
    AND "element" = 'padraogoverno01'
    AND "client_id" = 0
);

UPDATE "#__extensions"
SET "enabled" = 1,
    "params" = '{"cor":"azul","font_style_url":"{LOCALFONT}","icon_style":"bitmap-portal-brasil","denominacao":"Denomina\u00e7\u00e3o do \u00f3rg\u00e3o","nome_principal":"Nome principal","subordinacao":"subordina\u00e7\u00e3o","classe_nome_principal":"","emblema":"","rodape_acesso_informacao":1,"rodape_logo_brasil":1,"mensagem_final_ferramenta":"","clear_default_javascript":1,"local_mainscript":"footer","anexar_js_barra2014":"1","endereco_js_barra2014":"https:\/\/barra.brasil.gov.br\/barra.js?cor=verde","google_analytics_id":"","google_analytics_domain_name":"","google_analytics_allow_linker":0,"allow_set_color":"0","meta_generator":""}'
WHERE "type" = 'template'
  AND "element" = 'padraogoverno01'
  AND "client_id" = 0;

UPDATE "#__template_styles"
SET "home" = '0'
WHERE "client_id" = 0;

INSERT INTO "#__template_styles" ("template", "client_id", "home", "title", "inheritable", "parent", "params")
SELECT 'padraogoverno01', 0, '0', 'padraogoverno01 - Default', 1, '', '{"cor":"azul","font_style_url":"{LOCALFONT}","icon_style":"bitmap-portal-brasil","denominacao":"Denomina\u00e7\u00e3o do \u00f3rg\u00e3o","nome_principal":"Nome principal","subordinacao":"subordina\u00e7\u00e3o","classe_nome_principal":"","emblema":"","rodape_acesso_informacao":1,"rodape_logo_brasil":1,"mensagem_final_ferramenta":"","clear_default_javascript":1,"local_mainscript":"footer","anexar_js_barra2014":"1","endereco_js_barra2014":"https:\/\/barra.brasil.gov.br\/barra.js?cor=verde","google_analytics_id":"","google_analytics_domain_name":"","google_analytics_allow_linker":0,"allow_set_color":"0","meta_generator":""}'
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__template_styles"
  WHERE "template" = 'padraogoverno01'
    AND "client_id" = 0
);

UPDATE "#__template_styles"
SET "home" = '1',
    "title" = 'padraogoverno01 - Default',
    "inheritable" = 1,
    "parent" = '',
    "params" = '{"cor":"azul","font_style_url":"{LOCALFONT}","icon_style":"bitmap-portal-brasil","denominacao":"Denomina\u00e7\u00e3o do \u00f3rg\u00e3o","nome_principal":"Nome principal","subordinacao":"subordina\u00e7\u00e3o","classe_nome_principal":"","emblema":"","rodape_acesso_informacao":1,"rodape_logo_brasil":1,"mensagem_final_ferramenta":"","clear_default_javascript":1,"local_mainscript":"footer","anexar_js_barra2014":"1","endereco_js_barra2014":"https:\/\/barra.brasil.gov.br\/barra.js?cor=verde","google_analytics_id":"","google_analytics_domain_name":"","google_analytics_allow_linker":0,"allow_set_color":"0","meta_generator":""}'
WHERE "template" = 'padraogoverno01'
  AND "client_id" = 0;

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'Português do Brasil (pt-BR) Pacote de idioma', 'package', 'pkg_pt-BR', '', '', 0, 1, 1, 0, 0, '{"name":"Português do Brasil (pt-BR) Pacote de idioma","type":"package","version":"6.0.3.1","packagename":"pt-BR"}', '{}', '', 0, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__extensions"
  WHERE "type" = 'package'
    AND "element" = 'pkg_pt-BR'
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT "extension_id", 'Brazilian Portuguese (pt-BR)', 'language', 'pt-BR', '', '', 0, 1, 1, 0, 0, '{"name":"Brazilian Portuguese (pt-BR)","type":"language","version":"6.0.3"}', '{}', '', 0, 0
FROM "#__extensions"
WHERE "type" = 'package'
  AND "element" = 'pkg_pt-BR'
  AND NOT EXISTS (
    SELECT 1
    FROM "#__extensions" AS language_extension
    WHERE language_extension."type" = 'language'
      AND language_extension."element" = 'pt-BR'
      AND language_extension."client_id" = 0
  );

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT "extension_id", 'Brazilian Portuguese (pt-BR)', 'language', 'pt-BR', '', '', 1, 1, 1, 0, 0, '{"name":"Brazilian Portuguese (pt-BR)","type":"language","version":"6.0.3"}', '{}', '', 0, 0
FROM "#__extensions"
WHERE "type" = 'package'
  AND "element" = 'pkg_pt-BR'
  AND NOT EXISTS (
    SELECT 1
    FROM "#__extensions" AS language_extension
    WHERE language_extension."type" = 'language'
      AND language_extension."element" = 'pt-BR'
      AND language_extension."client_id" = 1
  );

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "changelogurl", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT "extension_id", 'Brazilian Portuguese (pt-BR)', 'language', 'pt-BR', '', '', 2, 1, 1, 0, 0, '{"name":"Brazilian Portuguese (pt-BR)","type":"language","version":"6.0.3"}', '{}', '', 0, 0
FROM "#__extensions"
WHERE "type" = 'package'
  AND "element" = 'pkg_pt-BR'
  AND NOT EXISTS (
    SELECT 1
    FROM "#__extensions" AS language_extension
    WHERE language_extension."type" = 'language'
      AND language_extension."element" = 'pt-BR'
      AND language_extension."client_id" = 2
  );

INSERT INTO "#__languages" ("lang_code", "title", "title_native", "sef", "image", "description", "metakey", "metadesc", "sitename", "published", "access", "ordering")
SELECT 'pt-BR', 'Portuguese (Brazil)', 'Português (Brasil)', 'pt-br', 'pt_br', '', '', '', '', 1, 1, 2
WHERE NOT EXISTS (
  SELECT 1
  FROM "#__languages"
  WHERE "lang_code" = 'pt-BR'
);

UPDATE "#__extensions"
SET "params" = '{"administrator":"pt-BR","site":"pt-BR"}'
WHERE "type" = 'component'
  AND "element" = 'com_languages'
  AND "client_id" = 1;

UPDATE "#__extensions"
SET "enabled" = 0
WHERE "type" = 'plugin'
  AND "folder" = 'sampledata'
  AND "element" IN ('blog', 'multilang')
  AND "client_id" = 0;
