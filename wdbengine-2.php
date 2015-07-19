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
	<link rel="stylesheet" type="text/css" href="../yui/build/treeview/assets/skins/sam/treeview.css"> 
	<link rel="stylesheet" type="text/css" href="../yui/build/button/assets/skins/sam/button.css" /> 

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
	<script type="text/javascript" src="../yui/build/button/button-min.js"></script> 
	
    <script type="text/javascript" src="../yui/build/container/container_core.js"></script> 
    <script type="text/javascript" src="../yui/build/treeview/treeview.js"></script> 
	
	<link rel="stylesheet" type="text/css" href="./estilos/wdbengine.css" />   
	
	<style>
		.imgBtnDelete {
			width: 16px;
			height: 16px;
			float: left;
			cursor: pointer;
		}
		
		.yui-button#addnewrow button {
			padding-left: 2em;
			background: url(estilos/add.png) 5% 50% no-repeat;
		}
		
		.yui-button#createtable button {
			padding-left: 2em;
			background: url(estilos/addt.gif) 5% 50% no-repeat;
		}
		
		.table-name {
			/*margin: 0px, 20px, 0px, 0px;*/
			padding: 10px;
			*border: 1px solid #99F;
			*background-color: #DDF;
		}
		
		.yui-skin-sam .yui-dt caption {
			*color: black;
			font-size: 100%;
			*font-weight: normal;
			font-style: normal;
			*line-height: 1;
			*padding: 1em 0;
			text-align: left;
			padding: 10px;
			border: 1px solid #99F;
			background-color: #DDF;
		}
		
		#treediv { width:250px; position:relative; padding:1px; margin-top:0; }
		.icon-table { 
			display:block; 
			height: 19px; 
			padding-left: 30px; 
			float: left;
			*margin: 2px;
			background: transparent url(estilos/table.png) 0 0 no-repeat; 
			cursor: pointer;
		}
		*.htmlnodelabel { margin: 20px; }
		
		
		
	</style>
		
<script type="text/javascript"> 

var Dom = YAHOO.util.Dom;
 
YAHOO.util.Event.onDOMReady(function () {
	
	layout = new YAHOO.widget.Layout({
		minWidth: 200,
		minHeight: 300,
		units: [
			//{ position: 'top', height: 50, body: 'divTop', gutter: '5px', resize: false },
			//{ position: 'right', header: 'Help', width: 240, resize: true, proxy: true, body: 'divRight', gutter: '2px 5px', collapse: true, collapseSize: 25, scroll: true, maxWidth: 300, animate: false },
			{ position: 'left', header: 'Schema master', width: 240, resize: true, proxy: true, body: 'divLeft', gutter: '5px 5px 2px 5px', collapse: true, collapseSize: 25, scroll: true, maxWidth: 600, animate: false },
			{ position: 'center', header: 'Resultsets', body: 'divCenter', scroll: true, gutter: '5px 5px 2px 0px'},			
			{ position: 'bottom', header: 'Query exec [ctrl+enter]', height: 150, resize: true, body: 'divBottom', gutter: '5px 5px', collapse: true, animate: true }
		]
	});
	
	layout.on('render', function() {
		layout.getUnitByPosition('left').on('close', function() {
                closeLeft();
            });    
			
		//schemaMaster();
		//makeMenu();
		treemenu();
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
};
 
 
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
	if(e.keyCode == 10)
		executeQuerys();
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
};
	
function schemaMaster(){
	
	var db = openDB();
	Dom.get('masterList').innerHTML = "";						
	
	try {
		if (db) {
			db.transaction(function(tx) {
				tx.executeSql("SELECT * FROM sqlite_master WHERE tbl_name != '__WebKitDatabaseInfoTable__' ", [], function(tx, result) {
					for (var i = 0; i < result.rows.length; i++) {
						var row = result.rows.item(i);					
						Dom.get('masterList').innerHTML += "<li class='"+ row['type'] +"'><em></em><a href='#' onclick='dumpTable(this)'>" + row['name'] + "</a></li>";
					}					
				}, handleDbError);
			});
		}
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}	    
};

function treemenu() {};

YAHOO.util.Event.onAvailable("treediv", function () {

	var oTextNode;
    var oTextNodeMap = {};
	var oCurrentTextNode = null;
	var db = openDB();		
	
	var oTreeView = new YAHOO.widget.TreeView("treediv");
	var tables = new YAHOO.widget.TextNode("Tables", oTreeView.getRoot(), true);
	
	try {
		if (db) {
			db.transaction(function(tx) {
				tx.executeSql("SELECT * FROM sqlite_master WHERE tbl_name != '__WebKitDatabaseInfoTable__' ", [], function(tx, result) {
					for (var i = 0; i < result.rows.length; i++) {
						var row = result.rows.item(i);
						oTextNode = new YAHOO.widget.TextNode(row['name'], tables, false);
						YAHOO.util.Event.on(oTextNode.labelElId, 'click', dumpTable);						
						oTextNodeMap[oTextNode.labelElId] = oTextNode; 
						oTextNode.labelStyle = "icon-table";	
					}	
					oTreeView.draw();	
				}, handleDbError);
			});
		}
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}
	

	function addNode() {
		var sLabel = window.prompt("Enter a label for the new node: ", ""), oChildNode;
		if (sLabel && sLabel.length > 0) {
			oChildNode = new YAHOO.widget.TextNode(sLabel, oCurrentTextNode, false);
			oCurrentTextNode.refresh();
			oCurrentTextNode.expand();
			oTextNodeMap[oChildNode.labelElId] = oChildNode;
		}
	}

	function editNodeLabel() {
		var sLabel = window.prompt("Enter a new label for this node: ", oCurrentTextNode.getLabelEl().innerHTML);
		if (sLabel && sLabel.length > 0) { oCurrentTextNode.getLabelEl().innerHTML = sLabel;}
	}

	function deleteNode() {
		/*delete oTextNodeMap[oCurrentTextNode.labelElId];
		oTreeView.removeNode(oCurrentTextNode);*/
		alert(oCurrentTextNode.labelElId);
		oTreeView.draw();
	}
	
	function onTriggerContextMenu(p_oEvent) {
		var oTarget = this.contextEventTarget;
		/*var oTextNode = Dom.hasClass(oTarget, "ygtvlabel") ? oTarget : Dom.getAncestorByClassName(oTarget, "ygtvlabel");
		if (oTextNode) { oCurrentTextNode = oTextNodeMap[oTarget.id];}
		else { this.cancel();}*/
		oCurrentTextNode = oTextNodeMap[oTarget.id];
	}
	
	var oContextMenu = new YAHOO.widget.ContextMenu("mytreecontextmenu", { trigger: "treediv", lazyload: true, itemdata: [
							{ text: "create new table", onclick: { fn: tableCreator } },
							{ text: "Data base schema", onclick: { fn: schemaDb } },
							{ text: "Delete Node", onclick: { fn: deleteNode } }
						] });

	oContextMenu.subscribe("triggerContextMenu", onTriggerContextMenu);
});
 
function dumpTable(target){
	
	var db = openDB();
	Dom.get('divCenter').innerHTML = "";
	var table = target.currentTarget.innerHTML;
	
	try { 
		if (db) 
			db.transaction(function(tx) { tx.executeSql("select * from " + table, [], handleDbResult, handleDbError);});
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}
};
 
function executeQuerys(){

	var db = openDB();
	Dom.get('divCenter').innerHTML = "";
	
	try {
		if (db) {
			db.transaction(function(tx) {
				var querys = Dom.get('queryarea').value.split(";");
				for(i in querys){
					if(querys[i] != ""){
						tx.executeSql(querys[i], [], handleDbResult, handleDbError);
					}
				}
				schemaMaster();
			});
		}
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}	    
};

function handleDbResult(tx, result) {
	if(result.rows.length)
		buildTableView(result);
	else
		Dom.get('divCenter').innerHTML = "<p>0 rows fetched.</p>";
};

function handleDbError(tx, error) { 
	alert(error.message); 
	return; 
};

function buildTableView(result){
	
	var myData = [];
	var myFields = [];
	var myColumnDefs = [];

	for(var header in  result.rows.item(0)){
		var col = {key:header, sortable:true, resizeable:true};
		myColumnDefs.push(col);
		myFields.push(header);
	}
	
	for (var i = 0; i < result.rows.length; i++)
		myData.push(result.rows.item(i));
	
	var tableContainer = document.createElement('div'); 
	Dom.get('divCenter').appendChild(tableContainer);	

	var myDataSource = new YAHOO.util.DataSource(myData);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
	myDataSource.responseSchema = {fields: myFields};	
	
	var myDataTable = new YAHOO.widget.DataTable(tableContainer, myColumnDefs, myDataSource);						
	myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
	myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
	myDataTable.subscribe("rowClickEvent", myDataTable.onEventSelectRow);					
};

function tableCreator(){
	
	Dom.get('divCenter').innerHTML = "";
	
	var tableContainer = document.createElement('div'); 
	Dom.get('divCenter').appendChild(tableContainer);
	
	YAHOO.widget.DataTable.Formatter.fmtDelete = function(elCell, oRecord, oColumn, oData){
		elCell.innerHTML = '<img src="estilos/delete.png" class="imgBtnDelete">';
	};
	
	YAHOO.widget.DataTable.Formatter.fmtAddnew = function(elCell, oRecord, oColumn, oData){
		elCell.innerHTML = '<img src="estilos/add.png" class="imgBtnDelete">';
	};
	
	YAHOO.widget.DataTable.Formatter.fmtField = function(elCell, oRecord, oColumn, oData){
		elCell.innerHTML = "<input type='text'/>";
	};
	
	YAHOO.widget.DataTable.Formatter.fmtPrimary = function(elCell, oRecord, oColumn, oData){
		elCell.innerHTML = "<input type='text'/>";
	};
	
	var myColumnDefs = [
		{key:"field", label:"Field name", resizeable:true, formatter:"fmtField"},
		{key:"type", label:"Data type", resizeable:true, formatter:"dropdown", dropdownOptions:["TEXT","INTEGER","FLOAT", "DOUBLE", "DATETIME", "BLOB"]},
		{key:"primary", label: "Primary", resizeable:true, formatter:"checkbox"},
		{key:"auto", label:"Auto inc", resizeable:true, formatter:"checkbox"},
		{key:"notnull", label:"Not null", resizeable:true, formatter:"checkbox"},
		{key:"unique", label:"Unique", resizeable:true, formatter:"checkbox"},
		{key:"default", label:"Default value", resizeable:true, formatter:"fmtField"},
		{key:"delete", label: "Del", resizeable:true, formatter:"fmtDelete"},
		//{key:"addnew", label: "Add", resizeable:true, formatter:"fmtAddnew"}	
	];

	var myDataSource = new YAHOO.util.DataSource([{}]);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
	myDataSource.responseSchema = {fields: ["field", "type", "primary", "auto", "notnull", "unique", "default", "delete"]};
	
	var myDataTable = new YAHOO.widget.DataTable(tableContainer, myColumnDefs, myDataSource, {caption:"Table name <input type='text' id='tablename'/>"});	
	
	myDataTable.subscribe("cellClickEvent", function(ev) {		    	    
        var target = YAHOO.util.Event.getTarget(ev);                  
        var key = this.getColumn(target).key;                                             
		if(key == "delete")
			this.deleteRow(this.getRecord(target)); 
    });
	
	function onNewRowClick(p_oEvent) {
		myDataTable.addRow({});
	}
	
	function onCreateTableClick(p_oEvent) {
		
		var db = openDB();
		var tr = myDataTable.getFirstTrEl();
		var primarykeys = [];
		var defcolumn = [];
		
		while(tr){
			var field = tr.cells[0].firstChild.firstChild.value;
			var type = tr.cells[1].firstChild.firstChild.value;
			var primary = tr.cells[2].firstChild.firstChild.value;
			var auto = ({"on":" AUTOINCREMENT"})[tr.cells[3].firstChild.firstChild.value] || "";
			var notnull = ({"on":"NOT NULL"})[tr.cells[4].firstChild.firstChild.value] || "";
			var unique = ({"on":"UNIQUE"})[tr.cells[5].firstChild.firstChild.value] || "";
			var vdefault = tr.cells[6].firstChild.firstChild.value;
			
			if(vdefault) vdefault = "DEFAULT " + vdefault;
			if(primary) primarykeys.push(field + auto);
				
			var col = [field, type, notnull, unique, vdefault].join(" ");
			defcolumn.push(col);
			
			tr = myDataTable.getNextTrEl(tr);
		}
		
		if(primarykeys.length > 0)
			primarykeys = ", PRIMARY KEY (" + primarykeys.join(",") + ")";
		
		var tabledef = "CREATE TABLE "+ Dom.get("tablename").value + "(" + defcolumn.join(", ") + primarykeys + ")";
		Dom.get('queryarea').value = tabledef;
		
		try { 
			if (db) 
				db.transaction(function(tx) { tx.executeSql(tabledef, [], function (tx, result) {
					schemaMaster();
				}, handleDbError);});
		}	
		catch(e){
			alert('DB Error:' + e.message + '.');
		}
	}
	
	var tableControls = document.createElement('div');
	tableControls.setAttribute("class", "table-name");
	Dom.get('divCenter').appendChild(tableControls);
	
	var addNewRow = new YAHOO.widget.Button({label:"Add new field", id:"addnewrow", container:tableControls });					
	addNewRow.on("click", onNewRowClick);
	
	var createTable = new YAHOO.widget.Button({label:"Create table", id:"createtable", container:tableControls });
	createTable.on("click", onCreateTableClick);
};

function schemaDb(){
	Dom.get('queryarea').value = "select * from sqlite_master";
	executeQuerys();
};
 
function buildQuerys(){
	
	container.innerHTML = "";
	
	var querys = queryarea.value.split(";");
	for(i in querys){
		container.innerHTML += 'tx.executeSql("'+querys[i]+'");<br/>';
	}
};
        
</script> 
 
</head> 
 
<body class="yui-skin-sam"> 
 
<div id="container"> 
    
    <div id="divTop"></div> 
    <div id="divLeft">
		<div id="treediv"></div>
        <div id="folder_list"> 
            <div class="wrap"> 
                <ul id="masterList"></ul>        
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