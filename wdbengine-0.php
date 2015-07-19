<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
	<title>WdbEngine</title> 
	
	<link rel="shortcut icon" href="../icono1.ico" /> 
 
    <link rel="stylesheet" type="text/css" href="../yui/build/fonts/fonts-min.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/container/assets/skins/sam/container.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/reset-fonts-grids/reset-fonts-grids.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/resize/assets/skins/sam/resize.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/layout/assets/skins/sam/layout.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/button/assets/skins/sam/button.css" /> 
	<link rel="stylesheet" type="text/css" href="../yui/build/menu/assets/skins/sam/menu.css"> 
	
	<link rel="stylesheet" type="text/css" href="../yui/build/datatable/assets/skins/sam/datatable.css" /> 

 
    <script type="text/javascript" src="../yui/build/yahoo-dom-event/yahoo-dom-event.js"></script> 
    <script type="text/javascript" src="../yui/build/dragdrop/dragdrop-min.js"></script> 
    <script type="text/javascript" src="../yui/build/container/container-min.js"></script> 
    <script type="text/javascript" src="../yui/build/selector/selector-min.js"></script> 
    <script type="text/javascript" src="../yui/build/element/element-min.js"></script> 
    <script type="text/javascript" src="../yui/build/resize/resize-min.js"></script> 
    <script type="text/javascript" src="../yui/build/animation/animation-min.js"></script> 
    <script type="text/javascript" src="../yui/build/layout/layout-min.js"></script> 
	<script type="text/javascript" src="../yui/build/menu/menu.js"></script> 
	
	<script type="text/javascript" src="../yui/build/datasource/datasource-min.js"></script> 
	<script type="text/javascript" src="../yui/build/datatable/datatable-min.js"></script> 
	
	<link rel="stylesheet" type="text/css" href="./estilos/director.css" />    
	
	<style type="text/css"> 
	table {
		margin: 1px;
	}
 
	td, th {
		border: 1px solid #999;
		padding: 0.1em 1em;
	}
	</style> 
		
<script type="text/javascript"> 
 
YAHOO.util.Event.onDOMReady(function () {
	
	var Dom = YAHOO.util.Dom;
	
	layout = new YAHOO.widget.Layout({
		minWidth: 200,
		minHeight: 300,
		units: [
		    //{ position: 'top', height: 50, body: 'divTop', gutter: '5px', resize: false },
			{ position: 'left', header: 'Schema master', width: 240, resize: true, proxy: true, body: 'divLeft', gutter: '2px 5px', collapse: true, collapseSize: 25, scroll: true, maxWidth: 300, animate: false },
			{ position: 'center', body: 'divCenter', scroll: true, gutter: '5px 5px 2px 0px'},
			//{ position: 'right', header: '', width: 240, resize: true, proxy: true, body: 'divRight', gutter: '2px 5px', collapse: true, collapseSize: 25, scroll: true, maxWidth: 300, animate: false },
			{ position: 'bottom', header: 'Query exec', height: 150, resize: true, body: 'divBottom', gutter: '5px', collapse: true, animate:false },	
		]
	});
	
	layout.on('render', function() {
		layout.getUnitByPosition('left').on('close', function() {
                closeLeft();
            });    
			
		schemaMaster();
		makeMenu();
	});
	
	layout.render();
	//layout.getUnitByPosition('right').collapse();
	layout.getUnitByPosition('bottom').on('resize', function() {
		Dom.setStyle("queryarea", 'height', Dom.getStyle(this, 'height'));
	});
	
});

function makeMenu(){
	
	var oFieldContextMenuItemData = [
		{ text: "Create new trable", onclick: { fn: tableCreator } },
		{ text: "Data base schema", onclick: { fn: schemaDb } },
		{ text: "Data base code", onclick: { fn: null } }
	];
	
	var oFieldContextMenu = new YAHOO.widget.ContextMenu(
									"fieldcontextmenu",
									{
										trigger: "layout-doc",
										itemdata: oFieldContextMenuItemData,
										lazyload: true
									}
								);

	oFieldContextMenu.subscribe("render", onFieldMenuRender);
}

function tableCreator(){
	alert(this);	
}

function schemaDb(){
	queryarea.value = "select * from sqlite_master";
	executeQuerys(queryarea.value);
}
 
 
YAHOO.util.Event.on('folder_list', 'click', function(ev) {
    var tar = YAHOO.util.Event.getTarget(ev);
    if (tar.tagName.toLowerCase() != 'a') {
        tar = null;
    }
   
    if (tar && YAHOO.util.Selector.test(tar, '#folder_list ul li a')) {
        if (tar && tar.getAttribute('href', 2) == '#') {
            YAHOO.util.Dom.removeClass(YAHOO.util.Selector.query('#folder_list li'), 'selected');
            var feedName = tar.parentNode.className;
            YAHOO.util.Dom.addClass(tar.parentNode, 'selected');
        }
    }
});
 
 
YAHOO.util.Event.addListener("queryarea", "keypress",  function(e) {
    //YAHOO.util.Event.preventDefault(e);
	if(e.keyCode == 10)
		batchQuerys();
});
 
function openDB() {
 
	try{
		var dbSize = 4 * 1024 * 1024; 
		var db = openDatabase("db3k","1.0","db3k",dbSize);
		return db;	    
	}
	catch(e){
		alert('DB Error:' + e.message + '.');
	}	    
}
	
function schemaMaster(){
	
	var db = openDB();
	
	try {
		if (db) {
			db.transaction(function(tx) {
				tx.executeSql("SELECT * FROM sqlite_master WHERE tbl_name != '__WebKitDatabaseInfoTable__' ", [], function(tx, result) {
					masterList.innerHTML = "";						
					for (var i = 0; i < result.rows.length; i++) {
						var row = result.rows.item(i);					
						masterList.innerHTML += "<li class='spam'><em></em><a href='#' onclick='dumpTable(this)'>" + row['tbl_name'] + "</a></li>";						
					}
					//masterList.innerHTML += "</ul>";						
				}, function(tx, error) {
					alert('Failed to read from database - ' + error.message);
					return;
				});
			});
		}
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}	    
}
 
function dumpTable(link){
	executeQuerys("SELECT * FROM " + link.innerHTML);
}
 
function executeQuerys(query){
 
	var db = openDB();
	
	var myColumnDefs = [];
	var myfields = [];
	var data = [];
	
	try {
		if (db) {
			db.transaction(function(tx) {
				
				query = query.replace('select', 'select rowid as zyrowid,');
				tx.executeSql(query, [], function(tx, result) {		        
					
					for(var header in  result.rows.item(0)){
						var col = {key:header, sortable:true, resizeable:true, editor: new YAHOO.widget.TextareaCellEditor()};
						myColumnDefs.push(col);
						myfields.push(header);
					}
					
					for (var i = 0; i < result.rows.length; i++)
						data.push(result.rows.item(i));
				
					var myDataSource = new YAHOO.util.DataSource(data);
					myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
					myDataSource.responseSchema = {fields: myfields};	
					
					var highlightEditableCell = function(oArgs) {
						var elCell = oArgs.target;
						if(YAHOO.util.Dom.hasClass(elCell, "yui-dt-editable")) {
							this.highlightCell(elCell);
						}
					};
					
					var myDataTable = new YAHOO.widget.DataTable("query-table", myColumnDefs, myDataSource);						
					//myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
					//myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
					//myDataTable.subscribe("rowClickEvent", myDataTable.onEventSelectRow);	
					
					myDataTable.subscribe("cellMouseoverEvent", highlightEditableCell);
					myDataTable.subscribe("cellMouseoutEvent", myDataTable.onEventUnhighlightCell);
					//myDataTable.subscribe("cellClickEvent", myDataTable.onEventShowCellEditor);	
					
					myDataTable.hideColumn("zyrowid");
					
				}, function(tx, error) {
					alert('Failed to read from database - ' + error.message);
					return;
				});
			});
		}
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}	    
}

function buildTableView(result){
	
	var data = [];
	var myfields = [];
	var myColumnDefs = [];
	
	for(var header in  result.rows.item(0)){
		var col = {key:header, sortable:true, resizeable:true};
		myColumnDefs.push(col);
		myfields.push(header);
	}
	
	for (var i = 0; i < result.rows.length; i++)
		data.push(result.rows.item(i));
	
	var tableContainer = document.createElement('div'); 
	divCenter.appendChild(tableContainer);	

	var myDataSource = new YAHOO.util.DataSource(data);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
	myDataSource.responseSchema = {fields: myfields};	
	
	var myDataTable = new YAHOO.widget.DataTable(tableContainer, myColumnDefs, myDataSource);						
	myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
	myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
	myDataTable.subscribe("rowClickEvent", myDataTable.onEventSelectRow);					
}

 
function batchQuerys(){
	
	container.innerHTML = "";
	
	var querys = queryarea.value.split(";");
	for(i in querys){
		executeQuerys(querys[i]);
	}
}
 
function buildQuerys(){
	
	container.innerHTML = "";
	
	var querys = queryarea.value.split(";");
	for(i in querys){
		container.innerHTML += 'tx.executeSql("'+querys[i]+'");<br/>';
	}
}
        
</script> 
 
</head> 
 
<body class="yui-skin-sam"> 
 
<div id="container"> 
    
    <div id="divTop"> 
    
    <div id="divLeft"> 
        <div id="folder_list"> 
            <div class="wrap"> 
                <ul id="masterList"> 
                </ul>        
            </div> 
        </div> 
    </div> 
    
    <div id="divCenter">
		<div id="query-table"></div> 
    </div> 
    <div id="divRight"></div> 
    <div id="divBottom"> 
		<textarea id="queryarea" style="width:100%; height:150px;" spellcheck="false"></textarea> 
	</div> 
	
</div>	
</body> 
</html> 