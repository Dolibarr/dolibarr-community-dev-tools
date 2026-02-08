<?php require_once __DIR__ . '/inc/__tools_header.php';

require_once __DIR__ . '/../class/modulesManager.class.php';
require_once __DIR__ . '/../class/moduleLangFileManager.class.php';

$devToolScriptName =  'SQFToPHP';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('devcommunitytools'.$devToolScriptName));

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$moduleName = GETPOST('module', 'aZ09');	// Used by actions_setmoduleoptions.inc.php

$currentLang = GETPOST('used-lang', 'aZ09');
if(empty($currentLang)){
	$currentLang =  $langs->defaultlang;
}

llxHeader('', $devToolScriptName, '', '', 0, 0, [], ['devcommunitytools/css/devtools.css']);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : dol_buildpath('/devcommunitytools/admin/tools.php', 1)).'">'.$langs->trans("BackToToolsList").'</a>';

print load_fiche_titre($langs->trans($devToolScriptName), $linkback, 'title_setup');


?>

	<style>
		textarea.sql-to-php__textarea {
			width: 100%;
			height: 35vh;
		}


		pre.sql-to-php__pre {
			max-width: 100%;
			overflow-x: scroll;
			overflow-y: auto;
			white-space: pre;
		}

	</style>

<div>
	<p>
		This is a simple tool to convert a pretty-printed pure SQL query (for instance a query that you fine-tuned in
		your IDE or in PHPMyAdmin) into PHP code you cane paste in your Dolibarr project, while keeping the result
		readable and indented.
	</p>
	<p>There is no actual SQL parsing involved, only performs string replacements.
	   However, it works well enough to be used with most common Dolibar SQL queries.</p>

    <details class="bordered-details">
        <summary>Example :</summary>

		<div class="help-sql-container">
			<div class="box">
				<h4>What you paste</h4>
				<pre class="sql-to-php__pre" >SELECT product.ref, COUNT(product.ref) AS nb, SUM(tl.total_ht) AS total, AVG(tl.total_ht) AS avg
FROM llx_propal AS p,
     llx_propaldet AS tl,
     llx_product AS product
         INNER JOIN llx_societe_commerciaux AS sc ON p.fk_soc = sc.fk_soc AND sc.fk_user = 1
WHERE p.entity IN (1)
  AND p.rowid = tl.fk_propal
  AND tl.fk_product = product.rowid
  AND p.datep BETWEEN '2023-01-01 00:00:00' AND '2023-12-31 23:59:59'
GROUP BY product.ref
ORDER BY nb DESC;</pre>
			</div>
			<div class="box">
				<h4 >What you get</h4>
				<pre class="sql-to-php__pre" >$sql = /** @lang SQL */
     "SELECT product.ref, COUNT(product.ref) AS nb, SUM(tl.total_ht) AS total, AVG(tl.total_ht) AS avg"
     . " FROM " . $db->prefix() . "propal AS p,"
     . "      " . $db->prefix() . "propaldet AS tl,"
     . "      " . $db->prefix() . "product AS product"
     . "          INNER JOIN " . $db->prefix() . "societe_commerciaux AS sc ON p.fk_soc = sc.fk_soc AND sc.fk_user = 1"
     . " WHERE p.entity IN (1)"
     . "   AND p.rowid = tl.fk_propal"
     . "   AND tl.fk_product = product.rowid"
     . "   AND p.datep BETWEEN '2023-01-01 00:00:00' AND '2023-12-31 23:59:59'"
     . " GROUP BY product.ref"
     . " ORDER BY nb DESC;";
                </pre>
			</div>
		</div>


    </details>
    <br>
</div>
<h2>Paste SQL here</h2>
<textarea class="sql-to-php__textarea" id="ta1"></textarea>
<h2>Copy this and paste it in your PHP code for Dolibarr</h2>
<textarea class="sql-to-php__textarea" id="ta2"></textarea>
<details class="bordered-details" >
    <summary>HTML Markup</summary>
    <h2>Copy this and paste it in a text input that allows simple HTML markup</h2>
    <textarea class="sql-to-php__textarea" id="ta3"></textarea>
    <h2>Preview of HTML markup</h2>
    <div id="d4"></div>
</details>

<script type="text/javascript">
    let SQLKeywordRegexp = new RegExp(
        '\\b(SELECT|UPDATE|SET|UPDATE|INSERT INTO'
        + '|ORDER BY|DROP|FROM|WHERE|AND|OR|AS|NOT|IS NULL'
        + '|(?:LEFT |RIGHT |INNER |OUTER )?JOIN|ON|GROUP BY|LIMIT'
        + '|DISTINCT'
        + '|CURDATE|INTERVAL|SUM|COUNT|REGEX|LIKE|IN|COALESCE)\\b',
        'gi'
    );

    function upperCaseSQLKeywords(sql) {
        return sql.replace(SQLKeywordRegexp, (g) => g.toUpperCase());
    }

    /**
     *
     * @param {string} txt
     * @param {boolean} usedoublequotes
     * @param {string} dbprefix
     * @returns {string}
     */
    function prepareForDolibarrPHP(txt, usedoublequotes = true, dbprefix = '$db->prefix()') {
        txt = txt.replace(/\\/g, '\\\\');
        if (usedoublequotes) {
            txt = txt.replace(/"/g, '\\"');
        } else {
            txt = txt.replace(/'/g, '\\\'');
        }
        txt = txt.replace(/llx_/g, usedoublequotes ? `" . ${dbprefix} . "` : `' . ${dbprefix} . '`);
        return txt;
    }

    /**
     * Returns the SQL query wrapped in quotes, where 'llx_' is replaced
     * with $db->prefix(), single quotes are escaped etc.
     * @param {string} sql
     * @param {boolean} usedoublequotes
     * @param {string} dbprefix
     * @returns {string}
     */
    function makePHPSQL(sql, usedoublequotes = true, dbprefix = '$db->prefix()') {
        sql = sql.replace(/\r\n/g, '\n');
        sql = upperCaseSQLKeywords(sql);
        lines = sql.split('\n');
        lines = lines.map(function (line, i) {
            line = prepareForDolibarrPHP(line, usedoublequotes, dbprefix);
            if (!i) {
                return usedoublequotes ?
                    `$sql = /** @lang SQL */\n     "${line}"` :
                    `$sql = /** @lang SQL */\n     '${line}'`;
            } else {
                return usedoublequotes ?
                    `     . " ${line}"` :
                    `     . ' ${line}'`;
            }
        });
        return lines.join('\n') + ';';
    }

    /**
     * Returns the SQL query wrapped in some HTML markup for pasting
     * @param sql
     * @returns {string}
     */
    function wrapSQLInHTML(sql) {
        let boldUpperCase = (group) => '<b>' + group.toUpperCase() + '</b>';
        sql = sql.replace(/\r\n/g, '\n');
        sql = '<pre>' + sql.replace(SQLKeywordRegexp, boldUpperCase) + '</pre>';
        return sql;
    }

    window.addEventListener('load', function () {
        let ta = document.getElementById('ta1');
        let ta2 = document.getElementById('ta2');
        let ta3 = document.getElementById('ta3');
        setInterval(function () {
            ta2.value = makePHPSQL(ta.value);
            let mantisCode = wrapSQLInHTML(ta.value);
            ta3.value = mantisCode;
            document.getElementById('d4').innerHTML = mantisCode;
        }, 500);
    });


</script>

<div style="margin-top: 80px;"></div>

<?php


// Page end
print dol_get_fiche_end();
require_once __DIR__.'/inc/__tools_footer.php';
