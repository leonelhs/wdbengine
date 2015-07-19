<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
	<title>WdbEngine</title> 
	
	<link rel="shortcut icon" href="icono1.ico" /> 
 
    <link rel="stylesheet" type="text/css" href="../yui/build/fonts/fonts-min.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/container/assets/skins/sam/container.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/reset-fonts-grids/reset-fonts-grids.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/resize/assets/skins/sam/resize.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/layout/assets/skins/sam/layout.css" /> 
    <link rel="stylesheet" type="text/css" href="../yui/build/button/assets/skins/sam/button.css" /> 
	<link rel="stylesheet" type="text/css" href="../yui/build/menu/assets/skins/sam/menu.css"> 
	<link rel="stylesheet" type="text/css" href="../yui/build/datatable/assets/skins/sam/datatable.css" /> 
	<link rel="stylesheet" type="text/css" href="../yui/build/treeview/assets/skins/sam/treeview.css"> 
	<!--<link rel="stylesheet" type="text/css" href="../yui/build/button/assets/skins/sam/button.css" /> -->

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

		
<script type="text/javascript"> 

var Dom = YAHOO.util.Dom;
var oTextNodeMap = {};
var oCurrentTextNode = null;
var oTreeView;
 
YAHOO.util.Event.onDOMReady(function () {
	
	layout = new YAHOO.widget.Layout({
		minWidth: 200,
		minHeight: 300,
		units: [
			//{ position: 'top', height: 50, body: 'divTop', gutter: '5px', resize: false },
			//{ position: 'right', header: 'Help', width: 240, resize: true, proxy: true, body: 'divRight', gutter: '2px 5px', collapse: true, collapseSize: 25, scroll: true, maxWidth: 300, animate: false },
			{ position: 'left', header: 'Schema master', width: 280, resize: true, proxy: true, body: 'divLeft', gutter: '5px 5px 2px 5px', collapse: true, collapseSize: 25, scroll: true, maxWidth: 600, animate: false },
			{ position: 'center', header: 'Results', body: 'divCenter', scroll: true, gutter: '5px 5px 2px 0px'},			
			{ position: 'bottom', header: 'Query exec [ctrl+enter || dblclick]', height: 150, resize: true, body: 'divBottom', gutter: '5px 5px', collapse: true, animate: false }
		]
	});
	
	layout.on('render', function() {
		layout.getUnitByPosition('left').on('close', function() {
			closeLeft();
        });
		infoWdbengine();
		schemaMaster();
		initContextMenu();	
	});
	
	layout.render();
	//layout.getUnitByPosition('right').collapse();
	layout.getUnitByPosition('bottom').on('resize', function() {
		Dom.setStyle("queryarea", 'height', Dom.getStyle(this, 'height'));
	});
	
});
 
YAHOO.util.Event.addListener("queryarea", "keypress",  function(e) {
	//YAHOO.util.Event.preventDefault(e);
	if(e.keyCode == 10)
		executeQuerys();
});

YAHOO.util.Event.addListener("queryarea", "dblclick",  function(e) {
	YAHOO.util.Event.preventDefault(e);
	executeQuerys();
});
 
function openDB() {
 
	try{
		var dbSize = 4 * 1024 * 1024; 
		var db = openDatabase("mydb","1.0","mydb",dbSize);
		return db;	    
	}
	catch(e){
		alert('DB Error:' + e.message + '.');
	}	    
};

function infoWdbengine(){
	
	var db = openDB();
	
	try { 
		if (db) 
			db.transaction(function(tx) { 
				tx.executeSql("CREATE TABLE IF NOT EXISTS wdbengine(version TEXT UNIQUE)");
				tx.executeSql("INSERT OR REPLACE INTO wdbengine(version) values('1.1')");
			});
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}

}
	
function schemaMaster(){
	
	var oTextNode;
	var db = openDB();		
	
	oTreeView = new YAHOO.widget.TreeView("schema-master");
	var tables = new YAHOO.widget.TextNode("Tables", oTreeView.getRoot(), true);
	var indexs = new YAHOO.widget.TextNode("Indexs", oTreeView.getRoot(), true);
	var triggers = new YAHOO.widget.TextNode("Triggers", oTreeView.getRoot(), true);
	var views = new YAHOO.widget.TextNode("Views", oTreeView.getRoot(), true);
	
	try {
		if (db) {
			db.transaction(function(tx) {
				tx.executeSql("SELECT * FROM sqlite_master WHERE tbl_name != '__WebKitDatabaseInfoTable__' ", [], function(tx, result) {
					for (var i = 0; i < result.rows.length; i++) {
						var row = result.rows.item(i);
						if(row['type'] == "table"){
							oTextNode = new YAHOO.widget.TextNode(row['name'], tables, false);
							YAHOO.util.Event.on(oTextNode.labelElId, 'click', dumpTable);						
							oTextNodeMap[oTextNode.labelElId] = oTextNode; 
							oTextNode.labelStyle = "icon-table";
						}
						if(row['type'] == "index"){
							oTextNode = new YAHOO.widget.TextNode(row['name'], indexs, false);
							YAHOO.util.Event.on(oTextNode.labelElId, 'click', dumpIndex);	
							oTextNodeMap[oTextNode.labelElId] = oTextNode; 
							oTextNode.labelStyle = "icon-index";
						}
						if(row['type'] == "trigger"){
							oTextNode = new YAHOO.widget.TextNode(row['name'], triggers, false);
							YAHOO.util.Event.on(oTextNode.labelElId, 'click', dumpTrigger);						
							oTextNodeMap[oTextNode.labelElId] = oTextNode;
							oTextNode.labelStyle = "icon-trigger";
						}
						if(row['type'] == "view"){
							oTextNode = new YAHOO.widget.TextNode(row['name'], views, false);
							YAHOO.util.Event.on(oTextNode.labelElId, 'click', dumpTable);						
							oTextNodeMap[oTextNode.labelElId] = oTextNode;
							oTextNode.labelStyle = "icon-view";
						}	
					}	
					oTreeView.draw();	
				}, handleDbError);
			});
		}
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}	    
};

function initContextMenu(){
	
	function contextInsertRecord(){
		if(oCurrentTextNode.type == "TABLE ")
			insertNewRecord(oCurrentTextNode.label);
	}
	
	function onTriggerContextMenu(p_oEvent) {
		var oTarget = this.contextEventTarget;
		oCurrentTextNode = oTextNodeMap[oTarget.id];
		
		if(Dom.hasClass(oTarget, "icon-table"))
			oCurrentTextNode.type = "TABLE ";
		if(Dom.hasClass(oTarget, "icon-index"))
			oCurrentTextNode.type = "INDEX ";
		if(Dom.hasClass(oTarget, "icon-trigger"))
			oCurrentTextNode.type = "TRIGGER ";
		if(Dom.hasClass(oTarget, "icon-view"))
			oCurrentTextNode.type = "VIEW ";	
	}
	
	
	var oContextMenu = new YAHOO.widget.ContextMenu("mytreecontextmenu", { trigger: "schema-master", lazyload: true, itemdata: [
							{ text: "Create table", onclick: { fn: tableCreator } },
							{ text: "Insert record", onclick: { fn: contextInsertRecord } },
							{ text: "Schema master", onclick: { fn: schemaDb } },
							{ text: "Ger raw data", onclick: { fn: getRawdata } },
							{ text: "Drop element", onclick: { fn: deleteElement } }
						] });

	oContextMenu.subscribe("triggerContextMenu", onTriggerContextMenu);
}
 
function dumpTable(target){
	
	var db = openDB();
	Dom.get('divCenter').innerHTML = "";
	var table = target.currentTarget.innerHTML;
	
	try { 
		if (db) 
			db.transaction(function(tx) { tx.executeSql("select * from " + table, [], function(tx, result){
				if(result.rows.length)
					buildTableView(result);
				else
					insertNewRecord(table);	
			}, handleDbError);});
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}
};

function dumpTrigger(target){
	
	var db = openDB();
	Dom.get('divCenter').innerHTML = "";
	var element = target.currentTarget.innerHTML;
	
	try { 
		if (db) 
			db.transaction(function(tx) { tx.executeSql("select sql from sqlite_master where type = 'trigger' and name = '" + element + "'", [], function(tx, result){
				buildTableView(result);	
			}, handleDbError);});
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}
};

function dumpIndex(target){
	
	var db = openDB();
	Dom.get('divCenter').innerHTML = "";
	var element = target.currentTarget.innerHTML;
	
	try { 
		if (db) 
			db.transaction(function(tx) { tx.executeSql("select sql from sqlite_master where type = 'index' and name = '" + element + "'", [], function(tx, result){
				buildTableView(result);
			}, handleDbError);});
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}
};

function insertNewRecord(table){
		
		var db = openDB();
		Dom.get('divCenter').innerHTML = "";
		
		try { 
			if (db){ 
				db.transaction(function(tx) {
					tx.executeSql('select * from (select "hide_null_empty_column") left join '+ table +' b on -1 = b.rowid',[], function(tx, result){							
						var myDataTable = handleDbResult(tx, result);
						myDataTable.removeColumn("hide_null_empty_column");
						delete(myDataTable.getRecord(0)._oData["hide_null_empty_column"]);	
						myDataTable.subscribe("cellClickEvent", myDataTable.onEventShowCellEditor); 
						
						var tableControls = document.createElement('div');
						tableControls.setAttribute("class", "table-name");
						Dom.get('divCenter').appendChild(tableControls);
						
						function onSaveRecordClick(p_oEvent) {								
							var cols = [], vals = [];
							var record = myDataTable.getRecord(0)._oData;
							for(var i in record){
								cols.push(i);
								vals.push(record[i]);
							}	
							
							var insert = "INSERT INTO " + table + "('" + cols.reverse().join("','") + "') values('" + vals.reverse().join("','") + "');";
							Dom.get('queryarea').value = insert;
							alert(insert);								
							executeQuerys();
						}
						
						var saveNewRecord = new YAHOO.widget.Button({label:"Insert new record", id:"savenewrecord", container:tableControls });					
						saveNewRecord.on("click", onSaveRecordClick);
					}, handleDbError);
				});
			}	
		}	
		catch(e){
			alert('DB Error:' + e.message + '.');
		}
}

function deleteElement(){

	var db = openDB();
		
	try { 
		if (db) 
			db.transaction(function(tx) { tx.executeSql("DROP " + oCurrentTextNode.type + oCurrentTextNode.label, [], function(){
				schemaMaster();
			}, handleDbError);});
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
				if(Dom.get('queryarea').value.toUpperCase().indexOf("TRIGGER") != -1)
					tx.executeSql(Dom.get('queryarea').value, [], handleDbResult, handleDbError);
				else{	
					var querys = Dom.get('queryarea').value.split(";");				
					for(i in querys){
						if(querys[i] != ""){
							tx.executeSql(querys[i], [], handleDbResult, handleDbError);
						}
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
	return buildTableView(result);	
};

function handleDbError(tx, error) {

	var triggererror = "";
	if(Dom.get('queryarea').value.toUpperCase().indexOf("TRIGGER") != -1)
			triggererror = "\nby the way, create trigger sentence is available one at a time and alone.";
			
	if(error.code != 1)
		alert(error.message + triggererror); 
		
	return; 
};

function buildTableView(result){
	
	var myData = [];
	var myFields = [];
	var myColumnDefs = [];
	
	try{
		for(var header in  result.rows.item(0)){
			var col = {key:header, sortable:true, resizeable:true, editor: new YAHOO.widget.TextboxCellEditor({disableBtns:true})};
			myColumnDefs.push(col);
			myFields.push(header);
		}
	
		for (var i = 0; i < result.rows.length; i++)
			myData.push(result.rows.item(i));
			
	}catch(e){
		myFields = ["result"];
		myColumnDefs = [{key:"result"}];
		myData = [{"result": result.rowsAffected + " rows affected or fetched."}];
	}
	
	var tableContainer = document.createElement('div'); 
	Dom.get('divCenter').appendChild(tableContainer);	

	var myDataSource = new YAHOO.util.DataSource(myData);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
	myDataSource.responseSchema = {fields: myFields};	
	
	var myDataTable = new YAHOO.widget.DataTable(tableContainer, myColumnDefs, myDataSource);	
	myDataTable.subscribe("rowMouseoverEvent", myDataTable.onEventHighlightRow);
	myDataTable.subscribe("rowMouseoutEvent", myDataTable.onEventUnhighlightRow);
	
	return myDataTable;
};

function tableCreator(){
	
	Dom.get('divCenter').innerHTML = "";
	
	var tableContainer = document.createElement('div'); 
	Dom.get('divCenter').appendChild(tableContainer);
	
	YAHOO.widget.DataTable.Formatter.fmtDelete = function(elCell, oRecord, oColumn, oData){
		elCell.innerHTML = '<img src="estilos/delete.png" class="imgBtnDelete">';
	};
	
	var myColumnDefs = [
		{key:"field", label:"Field name", resizeable:true, editor: new YAHOO.widget.TextboxCellEditor({disableBtns:true})},
		{key:"type", label:"Data type", resizeable:true, editor: new YAHOO.widget.DropdownCellEditor({dropdownOptions:["TEXT","INTEGER","FLOAT", "DOUBLE", "DATETIME", "BLOB"],disableBtns:true})},
		{key:"primary", label: "Primary", resizeable:true, editor: new YAHOO.widget.CheckboxCellEditor({checkboxOptions:["yes"],disableBtns:true})},
		{key:"auto", label:"Auto inc", resizeable:true, editor: new YAHOO.widget.CheckboxCellEditor({checkboxOptions:["yes"],disableBtns:true})},
		{key:"notnull", label:"Not null", resizeable:true, editor: new YAHOO.widget.CheckboxCellEditor({checkboxOptions:["yes"],disableBtns:true})},
		{key:"unique", label:"Unique", resizeable:true, editor: new YAHOO.widget.CheckboxCellEditor({checkboxOptions:["yes"],disableBtns:true})},
		{key:"default", label:"Default value", resizeable:true, editor: new YAHOO.widget.TextboxCellEditor({disableBtns:true})},
		{key:"delete", label: "Del", resizeable:true, formatter:"fmtDelete"}
	];

	var myDataSource = new YAHOO.util.DataSource([{}]);
	myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
	myDataSource.responseSchema = {fields: ["field", "type", "primary", "auto", "notnull", "unique", "default", "delete"]};
	
	var myDataTable = new YAHOO.widget.DataTable(tableContainer, myColumnDefs, myDataSource, {caption:"Table name <input type='text' id='tablename'/>"});	
	
	myDataTable.subscribe("cellClickEvent", myDataTable.onEventShowCellEditor);
	
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
		
		if(Dom.get("tablename").value == ""){
			alert("table name can't be empty.");
			return;
		}
		
		while(tr){
			
			var rowdef = myDataTable.getRecord(tr)._oData
			
			var field = rowdef["field"];
			var type = rowdef["type"];
			var primary = rowdef["primary"];
			var auto = ({"yes":" AUTOINCREMENT"})[rowdef["auto"]] || "";
			var notnull = ({"yes":"NOT NULL"})[rowdef["notnull"]] || "";
			var unique = ({"yes":"UNIQUE"})[rowdef["unique"]] || "";
			var vdefault = rowdef["default"];
			
			if(vdefault && vdefault != "") vdefault = "DEFAULT " + vdefault;
			if(primary == "yes") primarykeys.push(field + auto);
				
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

function getRawdata(){
	
	var db = openDB();
	Dom.get('divCenter').innerHTML = "";
		
	try { 
		if (db) 
			db.transaction(function(tx) { tx.executeSql("SELECT * FROM " + oCurrentTextNode.label, [], function(tx, result){
				var myData = [];				
				for (var i = 0; i < result.rows.length; i++){
					var data = result.rows.item(i);
					myData = [];
					var row = document.createElement("p");
					for(var j in data){						
						myData.push(data[j]);
					}	
					row.innerHTML = myData.join(",");
					Dom.get('divCenter').appendChild(row);	
				}					
			}, handleDbError);});
	}	
	catch(e){
		alert('DB Error:' + e.message + '.');
	}
}

function schemaDb(){
	Dom.get('queryarea').value = "select * from sqlite_master";
	executeQuerys();
};
 
</script> 
 
</head> 
 
<body class="yui-skin-sam"> 
 
<div id="container"> 
    
    <div id="divTop"></div> 
    <div id="divLeft">
		<div id="schema-master"></div>
    </div> 
    
    <div id="divCenter"></div> 
    <div id="divRight"></div> 
    <div id="divBottom"> 
		<textarea id="queryarea" style="width:100%; height:150px;" spellcheck="false"></textarea> 
	</div>
	
</div>
</body> 
</html> 
