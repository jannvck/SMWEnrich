/*
 * SMWEnrich Client JavaScript source code.
 * 
 * This file contains 2 sections as the client code
 * is split in the SMWEnrich JavaScript API and the
 * SMWEnrich Client GUI both implemented in JavaScript.
 *
 */


/*
 *
 * SMWEnrich JavaScript API
 *
 */
 
function EntitySelection(id, name, description) {
	this.id = id;
	this.name = name;
	this.description = description;
}
EntitySelection.prototype.setID = function(id) {
	this.id = id;
};
EntitySelection.prototype.getID = function() {
	return this.id;
};
EntitySelection.prototype.getName = function() {
	return this.name;
};
EntitySelection.prototype.setName = function(name) {
	this.name = name;
};
EntitySelection.prototype.getDescription = function() {
	return this.description;
};
EntitySelection.prototype.setDescription = function(description) {
	this.description = description;
};
EntitySelection.fromJSONArray = function(json) {
	var selections = [];
	if(json.empty!="true") {
		$.each(json, function(i, selection) {
				selections.push(
					new EntitySelection(
						selection.id,
						selection.name,
						selection.description));
		});
	}
	return selections;
};

function EntitySelections() {
}
EntitySelections.prototype.getEntitySelections = function() {
	console.log("getEntitySelections()...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichentities&format=json&list=true",
			type : "GET"
	});
};
EntitySelections.prototype.addEntitySelection = function(selection) {
	console.log("addEntitySelection("+selection.getName()+", "+selection.getDescription()+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichentities&format=json&add=true",
			type : "POST",
			data : "name="+selection.getName()+"&description="+selection.getDescription()
			//contentType : "text/json" // FIXME wrong content type
	});
};
EntitySelections.prototype.removeEntitySelection = function(selection) {
	this.removeEntitySelectionById(selection.getID());
};
EntitySelections.prototype.removeEntitySelectionByID = function(selectionId) {
	console.log("removeEntitySelection("+selectionId+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichentities&format=json&remove=true",
			type : "POST",
			data : "selection="+selectionId
			//contentType : "text/json" // FIXME wrong content type
	});
};
EntitySelections.prototype.getEntities = function(selection) {
	this.getEntitiesBySelectionID(selection.getID());
};
EntitySelections.prototype.getEntitiesBySelectionID = function(selectionId) {
	console.log("getEntities("+selectionId+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichentities&format=json"+
				"&list=true&selection="+selectionId,
			type : "GET"
	});
};
EntitySelections.prototype.addEntity = function(selection, entityId) {
	this.addEntityBySelectionId(selection.getID(), entityId);
};
EntitySelections.prototype.addEntityBySelectionID = function(selectionId, entityId) {
	console.log("addEntity("+selectionId+", "+entityId+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichentities&format=json&add=true",
			type : "POST",
			data : "selection="+selectionId+"&name="+entityId
	});
};
EntitySelections.prototype.removeEntity = function(selection, entityId) {
	this.removeEntityBySelectionId(selection.getID(), entityId);
};
EntitySelections.prototype.removeEntityBySelectionID = function(selectionId, entityId) {
	console.log("removeEntity("+selectionId+", "+entityId+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichentities&format=json&remove=true",
			type : "POST",
			data : "selection="+selectionId+"&name="+entityId
	});
};

function DataSource(id, name, url) {
	this.id = id;
	this.name = name;
	this.url = url;
}
DataSource.prototype.setID = function(id) {
	this.id = id;
};
DataSource.prototype.getID = function() {
	return this.id;
};
DataSource.prototype.getName = function() {
	return this.name;
};
DataSource.prototype.setName = function(name) {
	this.name = name;
};
DataSource.prototype.getURL = function() {
	return this.url;
};
DataSource.prototype.setURL = function(url) {
	this.url = url;
};
DataSource.fromJSONArray = function(json) {
	var dataSources = [];
	if(json.empty!="true") {
		$.each(json, function(i, dataSource) {
				console.log("adding data source"+
					" with name="+dataSource.name+", url="+dataSource.url);
				dataSources.push(
					new DataSource(
						dataSource.id,
						dataSource.name,
						dataSource.url));
		});
	}
	return dataSources;
};

function DataSources() {
}
DataSources.prototype.getDataSources = function() {
	console.log("getDataSources()...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichdatasources&format=json&list=true",
			type : "GET"
	});
};
DataSources.prototype.addDataSource = function(dataSource) {
	console.log("addDataSource("+dataSource.getName()+", "+dataSource.getURL()+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichdatasources&format=json&add=true",
			type : "POST",
			data : "name="+dataSource.getName()+"&url="+dataSource.getURL()
	});
};
DataSources.prototype.removeDataSource = function(dataSource) {
	console.log("removeDataSource("+dataSource+")");
	this.removeDataSourceByID(dataSource.getID());
};
DataSources.prototype.removeDataSourceByID = function(dataSourceId) {
	console.log("removeDataSource("+dataSourceId+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichdatasources&format=json&remove=true",
			type : "POST",
			data : "id="+dataSourceId
	});
};
DataSources.prototype.updateDataSource = function(dataSource) {
	console.log("updateDataSource("+dataSource+")...");
	// TODO: extend server-side API to support updates instead of remove, add
	this.removeDataSourceByID(dataSource.getID());
	this.addDataSource(dataSource);
};

function ReferenceLinkGroup(id, name, description) {
	this.id = id;
	this.name = name;
	this.description = description;
}
ReferenceLinkGroup.prototype.getID = function() {
	return this.id;
};
ReferenceLinkGroup.prototype.getName = function() {
	return this.name;
};
ReferenceLinkGroup.prototype.setName = function(name) {
	this.name = name;
};
ReferenceLinkGroup.prototype.getDescription = function() {
	return this.description;
};
ReferenceLinkGroup.prototype.setDescription = function(description) {
	this.description = description;
};
ReferenceLinkGroup.fromJSONArray = function(json) {
	var groups = [];
	if(json.empty!="true") {
		$.each(json, function(i, group) {
				groups.push(
					new ReferenceLinkGroup(
						group.id,
						group.name,
						group.description));
		});
	}
	return groups;
};

function ReferenceLink(id, uri0, uri1) {
	this.id = id;
	this.uri0 = uri0;
	this.uri1 = uri1;
}
ReferenceLink.prototype.getID = function() {
	return this.id;
};
ReferenceLink.prototype.getURI0 = function() {
	return this.uri0;
};
ReferenceLink.prototype.setURI0 = function(uri) {
	this.uri0 = uri;
};
ReferenceLink.prototype.getURI1 = function() {
	return this.uri1;
};
ReferenceLink.prototype.setURI1 = function(uri) {
	this.uri1 = uri;
};
ReferenceLink.fromJSONArray = function(json) {
	var links = [];
	if(json.empty!="true") {
		$.each(json, function(i, link) {
				links.push(
					new ReferenceLink(
						link.id,
						link.name,
						link.url));
		});
	}
	return links;
};

function ReferenceLinks() {
}
ReferenceLinks.prototype.getLinkGroups = function() {
	console.log("getLinkGroups()...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichlinks&format=json&list=true",
			type : "GET"
	});
};
ReferenceLinks.prototype.addLinkGroup = function(group) {
	console.log("addLinkGroup("+group+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichlinks&format=json&add=true",
			type : "POST",
			data : "name="+group.getName()+
							"&description="+group.getDescription()
	});
};
ReferenceLinks.prototype.removeLinkGroup = function(group) {
	this.removeLinkGroupById(group.getID());
};
ReferenceLinks.prototype.removeLinkGroupByID = function(id) {
	console.log("removeLinkGroupById("+id+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichlinks&format=json&remove=true&group="+id,
			type : "GET"
	});
};
ReferenceLinks.prototype.getLinks = function(group) {
	this.getLinksByGroupID(group.getID());
};
ReferenceLinks.prototype.getLinksByGroupID = function(groupId) {
	console.log("getLinks("+groupId+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichlinks&format=json&list=true&group="+groupId,
			type : "GET"
	});
};
ReferenceLinks.prototype.addLink = function(group, link) {
	this.addLinkByGroupID(group.getID(), link);
};
ReferenceLinks.prototype.addLinkByGroupID = function(groupId, link) {
	console.log("addLinkByGroupID("+groupId+", "+link+")...");
	console.log("uri0="+link.getURI0());
	console.log("uri1="+link.getURI1());
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichlinks&format=json&add=true",
			type : "POST",
			data : "group="+groupId+
							"&uri0="+link.getURI0()+
							"&uri1="+link.getURI1()
	});
};
ReferenceLinks.prototype.removeLink = function(link) {
	this.removeLinkGroupById(link.getID());
};
ReferenceLinks.prototype.removeLinkByID = function(id) {
	console.log("removeLinkById("+id+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichlinks&format=json&remove=true",
			type : "POST",
			data : "link="+id
	});
};

function Cell(id, uri0, uri1, relation, measure) {
	this.id = id;
	this.uri0 = uri0;
	this.uri1 = uri1;
	this.relation = relation;
	this.measure = measure;
}
Cell.prototype.getID = function() {
	return this.id;
};
Cell.prototype.getURI0 = function() {
	return this.uri0;
};
Cell.prototype.setURI0 = function(uri) {
	this.uri0 = uri;
};
Cell.prototype.getURI1 = function() {
	return this.uri1;
};
Cell.prototype.setURI1 = function(uri) {
	this.uri1 = uri;
};
Cell.prototype.getRelation = function() {
	return this.relation;
};
Cell.prototype.setRelation = function(relation) {
	this.relation = relation;
};
Cell.prototype.getMeasure = function() {
	return this.measure;
};
Cell.prototype.setMeasure = function(measure) {
	this.measure = measure;
};
Cell.fromJSONArray = function(json) {
	var links = [];
	if(json.empty!="true") {
		$.each(json, function(i, link) {
				links.push(
					new Cell(
						link.id,
						link.entity0,
						link.entity1,
						link.relation,
						link.measure));
		});
	}
	return links;
};

function EntityMatchingResults() {
}
EntityMatchingResults.prototype.getLinksByJobID = function(jobId) {
	console.log("getLinks()...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichmatchingresults&format=json&list=true&job="+jobId,
			type : "GET"
	});
};
EntityMatchingResults.prototype.removeLinkByID = function(linkId) {
	console.log("removeLinkByID("+linkId+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichmatchingresults&format=json",
			type : "POST",
			data : "remove="+linkId
	});
};
EntityMatchingResults.prototype.publishByJobID = function(jobId) {
	console.log("publishByJobID("+jobId+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichmatchingresults&format=json&job="+jobId+"&publish=true",
			type : "GET"
	});
};

function Job(
		id,
		name,
		description,
		selectionId,
		linkGroupId,
		dataSourceId) {
	this.id = id;
	this.name = name;
	this.description = description;
	this.selectionId = selectionId;
	this.linkGroupId = linkGroupId;
	this.dataSourceId = dataSourceId;
}
Job.prototype.setID = function(id) {
	this.id = id;
};
Job.prototype.getID = function() {
	return this.id;
};
Job.prototype.setName = function(name) {
	this.name = name;
};
Job.prototype.getName = function() {
	return this.name;
};
Job.prototype.setDescription = function(description) {
	this.description = description;
};
Job.prototype.getDescription = function() {
	return this.description;
};
Job.prototype.setEntitySelectionID = function(id) {
	this.selectionId = id;
};
Job.prototype.getEntitySelectionID = function() {
	return this.selectionId;
};
Job.prototype.setReferenceLinkGroupID = function(id) {
	this.linkGroupId = id;
};
Job.prototype.getReferenceLinkGroupID = function() {
	return this.linkGroupId;
};
Job.prototype.setDataSourceID = function(id) {
	this.dataSourceId = id;
};
Job.prototype.getDataSourceID = function() {
	return this.dataSourceId;
};
Job.prototype.urlEncode = function() {
	var out = "id="+this.getID();
	out += "&name="+this.getName();
	out += "&description="+this.getDescription();
	// requests with 'null' as these parameter values will fail,
	// as the database schema does not permit that
	if(this.getEntitySelectionID() !== null) {
		out += "&selection="+this.getEntitySelectionID();
	}
	if(this.getReferenceLinkGroupID() !== null) {
		out += "&links="+this.getReferenceLinkGroupID();
	}
	if(this.getDataSourceID() !== null) {
		out += "&dataSource="+this.getDataSourceID();
	}
	return out;
};
Job.fromJSONArray = function(json) {
	var jobs = [];
	if(json.empty!="true") {
		$.each(json, function(i, job) {
				jobs.push(
					new Job(
						job.id,
						job.name,
						job.description,
						job.selectionId,
						job.linkGroupId,
						job.dataSourceId));
		});
	}
	return jobs;
};

function Jobs() {
}
Jobs.prototype.getJobs = function() {
	console.log("getJobs()...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichjobs&format=json&list=true",
			type : "GET"
	});
};
Jobs.prototype.addJob = function(job) {
	console.log("addJob("+job+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichjobs&format=json&add=true",
			type : "POST",
			data : job.urlEncode()
	});
};
Jobs.prototype.removeJob = function(job) {
	this.removeJobByID(job.getID());
};
Jobs.prototype.removeJobByID = function(id) {
	console.log("removeJobByID("+id+")...");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichjobs&format=json&remove=true",
			type : "POST",
			data : "id="+id
	});
};
Jobs.prototype.updateJob = function(job) {
	console.log("updateJob("+job+")");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichjobs&format=json&update=true",
			type : "POST",
			data : job.urlEncode()
	});
};
Jobs.prototype.startJob = function(job) {
	console.log("startJob("+job+")");
	return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/api.php?action=smwenrichjobs&format=json&start=true",
			type : "POST",
			data : "id="+job.getID()
	});
};

function SMWEnrichApi() {
}
SMWEnrichApi.prototype.matchingResults = new EntityMatchingResults();
SMWEnrichApi.prototype.entitySelections = new EntitySelections();
SMWEnrichApi.prototype.referenceLinks = new ReferenceLinks();
SMWEnrichApi.prototype.dataSources = new DataSources();
SMWEnrichApi.prototype.jobs = new Jobs();


/*
 *
 * SMWEnrich Client GUI, main client side logic
 *
 */
 
function SMWEnrichClient(api) {
	// required due to error in ECMAScript Language specification
	// see http://javascript.crockford.com/private.html
	var that = this;
	
	// some properties for convenience
	var currentJob;
	var currentSelection;
	var currentDataSource;
	var currentLinkGroup;
	// keep some references to instances as primitive caching
	var jobs = [];
	var entitySelections = [];
	var linkGroups = [];
	var dataSources = [];
	var results = [];
	
	/*
	 * List of events:
	 *
	 * initJobsDone
	 * initSlectionCoiceDone
	 * initEntityListDone
	 * initDataSourceSelectionDone
	 * initReferenceLinksEditorDone
	 * initResultViewDone
	 * initTabsDone
	 * initAllDone
	 *
	 * modifiedJobChosen - A job has been selected by the user
	 * modifiedJobName - The name of a job has been changed
	 * modifiedJobDescription
	 * modifiedCurrentJob - Current job has been changed in any way
	 * modifiedSelectionChosen - Entity list has been selected
	 * modifiedSelectionAdded - A new entity list has been added
	 * modifiedSelectionRemoved - Entity list has been removed
	 * modifiedEntityRemoved - Entity has been removed from a list
	 * modifiedEntityAdded - Entity has been added to a list
	 * modifiedDataSourceChosen
	 * modifiedDataSourceAdded
	 * modifiedDataSourceRemoved
	 * modifiedLinkGroupSelection - A link group has been selected
	 * modifiedLinkGroupAdded - A new link group has been added
	 * modifiedLinkGroupRemoving - A link group is about to be removed
	 * modifiedLinkGroupRemoved - A link group has been removed
	 * modifiedLinkGroupName - Reference links group name changed
	 * modifiedLinkGroupDescriptions
	 * modifiedLinkAdded
	 * modifiedLinkRemoved
	 * modifiedResultLinkRemoving - A result link is about to be removed
	 * modifiedResultLinkRemoved - A result link has been removed
	 * modifiedResultPublishing - Result links are about to be published
	 *
	 * updateEntityListComplete
	 * updateReferenceLinkListComplete
	 */
	 
	var listeners = [];
	function fire(event, context) {
		console.log(event);
		$.each(listeners, function(i, listener) {
				if(listener.event==event) {
					listener.callback(context);
				}
		});
	}
	this.on = function(event, f) {
		listener = {};
		listener.event = event;
		listener.callback = f;
		listeners.push(listener);
	};
	this.off = function(event, f) {
		listeners = $.grep(listeners, function(listener) {
				return listener.event != event && listener.callback != f;
		});
	};
	
	/*
	 * The following functions are responsible for the presentation
	 * which is speperated from logic defined later on.
	 */
	
	function printEntity(uri) {
		return '<li><div>'+uri+
				'<img class="removeEntity" '+
				'src="/smw-cora/extensions/smwEnrich/images/delete.png"/></div></li>'; // TODO dynamic url
	}
	function printEntities(jsonEntities) {
		var out = "<ul>";
		if(jsonEntities.empty!="true") {
			$.each(jsonEntities, function(i, entity) {
				out +=  printEntity(entity.id);
			});
		}
		out += "</ul>";
		return out;
	}
	function printReferenceLink(id, uri0, uri1) {
		return '<li><div class="referenceLink" id="'+id+'">'+
				'<div class="referenceLinkLocalURI" style="display: inline-block" contenteditable="true">'+uri0+'</div>'+
				'<div class="referenceLinkExternalURI" style="display: inline-block" contenteditable="true">'+uri1+'</div>'+
				'<img class="removeReferenceLink" '+
				'src="/smw-cora/extensions/smwEnrich/images/delete.png"/></li></div>'; // TODO dynamic url
	}
	function printReferenceLinks(jsonLinks) {
		var out = "<ul>";
		if(jsonLinks.empty!="true") {
			$.each(jsonLinks, function(i, link) {
				out +=  printReferenceLink(
					link.id,
					link.uri0,
					link.uri1);
			});
		}
		out += "</ul>";
		return out;
	}
	function printEntitySelection(id, name, description) {
		return '<div class="entitySelection">'+
			'<input name="entitySelection" type="radio" value="'+id+'"/>'+
			'<div class="entitySelectionName textfield" style="display: inline-block" contenteditable="true">'+name+'</div>'+
			'<div class="entitySelectionDescription textfield" style="display: inline-block" contenteditable="true">'+description+'</div>'+
			'<img class="removeSelection" src="/smw-cora/extensions/smwEnrich/images/delete.png"/>'+
			'</div>';
	}
	function printEntitySelections(selections) {
		var out = "<form>";
		$.each(selections, function(i, selection) {
				console.log("printing "+selection);
				out += printEntitySelection(
					selection.getID(),
					selection.getName(),
					selection.getDescription());
		});
		out += printEntitySelection("new", "New selection", "Description");
		out += "</form>";
		return out;
	}
	function printDataSource(id, name, url) {
		return '<div class="dataSources">'+ 
			'<input name="dataSourceSelection" style="display: inline-block" type="radio" value="'+id+'"/>'+
			'<div class="dataSourceName textfield" style="display: inline-block" contenteditable="true">'+name+'</div>'+
			'<div class="dataSourceURL textfield" style="display: inline-block" contenteditable="true">'+url+'</div>'+
			'<img class="removeDataSource" src="/smw-cora/extensions/smwEnrich/images/delete.png"/>'+
			'</div>';
	}
	function printDataSources(dataSources) {
		var out = "<form>";
		if(dataSources.empty!="true") {
			$.each(dataSources, function(i, dataSource) {
					out += printDataSource(
						dataSource.getID(),
						dataSource.getName(),
						dataSource.getURL());
			});
		}
		out += "</form>";
		return out;
	}
	function printLinkGroup(id, name, description) {
		return '<div class="linkGroups">'+
			'<input style="display: inline-block" name="linkSelection" type="radio" value="'+id+'"/>'+
			'<div class="linkGroupName textfield" style="display: inline-block" contenteditable="true">'+name+'</div>'+
			'<div class="linkGroupDescription textfield" style="display: inline-block" contenteditable="true">'+description+'</div>'+
			'<img class="removeLinkGroup" src="/smw-cora/extensions/smwEnrich/images/delete.png"/>'+
			'</div>';
	}
	function printLinkGroups(linkGroups) {
		var out = "<form>";
		if(linkGroups.empty!="true") {
			console.log("not empty...");
			$.each(linkGroups, function(i, linkGroup) {
					console.log("linkGroup="+linkGroup);
					console.log("linkGroup name="+linkGroup.getName());
					out += printLinkGroup(
						linkGroup.getID(),
						linkGroup.getName(),
						linkGroup.getDescription());
			});
		}
		out += printLinkGroup("new", "New link group", "Description");
		out += "</form>";
		return out;
	}
	function printJob(id, name, description) {
		return '<div class="block">'+
			'<input style="display: inline-block" name="jobSelection" type="radio" value="'+id+'"/>'+
			'<div style="display: inline-block" class="jobName textfield" contenteditable="true">'+name+'</div>'+
			'<div style="display: inline-block" class="jobDescription textfield" contenteditable="true">'+description+'</div>'+
			'<img class="removeJob" src="/smw-cora/extensions/smwEnrich/images/delete.png"/>'+
			'<input class="jobStartButton" type="button" id="buttonStartNewJob" value="start"/>'+
			'<div style="display: inline-block" class="jobProgress"><div class="progress-label">Working...</div></div>'+
			'</div>';
	}
	function printJobs(jsonJobs) {
		var out = "<form>"; 
		if(jsonJobs.empty!="true") {
			$.each(jsonJobs, function(i, job) {
					console.log("got job, name="+job.getName()+", selection="+
						job.getEntitySelectionID());
					out += printJob(
						job.getID(),
						job.getName(),
						job.getDescription());
			});
		}
		out += printJob("new", "&lt;new job name&gt;", "&lt;new job description&gt;");
		out += "</form>";
		return out;
	}
	function printResultLink(id, entity0, entity1, relation, measure) {
		return '<div class="resultLink block" id="'+id+'">'+
			'<div style="display: inline-block">'+
			'<a class="resultLinkEntity0" target="_blank" href="'+entity0+'">'+entity0+'</a></div>'+
			//'<div style="display: inline-block" class="resultLinkRelation">'+relation+'</div>'+
			'<div style="display: inline-block" class="resultLinkMeasure">&larr; '+measure+' &rarr;</div>'+
			'<div style="display: inline-block">'+
			'<a class="resultLinkEntity1" original-title="shit" target="_blank" href="'+entity1+'">'+entity1+'</a></div>'+
			'<img class="removeResultLink" src="/smw-cora/extensions/smwEnrich/images/delete.png"/>'+
			'</div>';
	}
	function printResultLinks(jsonLinks) {
		var out = ""; 
		if(jsonLinks.empty!="true") {
			$.each(jsonLinks, function(i, link) {
					out += printResultLink(
						link.getID(),
						link.getURI0(),
						link.getURI1(),
						link.getRelation(),
						link.getMeasure());
			});
		}
		return out;
	}
	
	/*
	 * Utility functions, used within initialization functions later on
	 */
	 
	function getJobByID(jobId) {
		var found = $.grep(that.jobs, function(job) {
				return job.getID() == jobId;
		});
		return found;
	}
	function removeJobByID(jobId) {
		that.jobs = $.grep(that.jobs, function(job) {
				return job.getID() != jobId;
		});
	}
	function getEntitySelectionByID(selectionId) {
		var found = $.grep(that.entitySelections, function(selection) {
				return selection.getID() == selectionId;
		});
		return found;
	}
	function removeEntitySelectionByID(selectionId) {
		that.dataSources = $.grep(that.entitySelections, function(selection) {
				return selection.getID() != selectionId;
		});
	}
	function getDataSourceByID(dataSourceId) {
		var found = $.grep(that.dataSources, function(dataSource) {
				return dataSource.getID() == dataSourceId;
		});
		return found;
	}
	function removeDataSourceByID(dataSourceId) {
		that.dataSources = $.grep(that.dataSources, function(dataSource) {
				return dataSource.getID() != dataSourceId;
		});
	}
	function getReferenceLinkGroupByID(linkGroupId) {
		var found = $.grep(that.linkGroups, function(linkGroup) {
				return linkGroup.getID() == linkGroupId;
		});
		return found;
	}
	function removeReferenceLinkGroupByID(linkGroupId) {
		that.linkGroups = $.grep(that.linkGroups, function(linkGroup) {
				return linkGroup.getID() != linkGroupId;
		});
	}
	function autocomplete(jqNode) {
		mw.loader.using("jquery.ui.autocomplete", function() {
			jqNode.autocomplete({
							source: function( request, response ) {
								var api = new mw.Api();
								api.get({
										action: "opensearch",
										search: request.term, // current value of user's input
										suggest: ''
								}).done(function(data) {
									response(data[1]); // set results as autocomplete options
								});
							}
					});
		});
		return jqNode;
	}
	function updateEntities(selectionId) {
		api.entitySelections.getEntitiesBySelectionID(selectionId).done(function(data) {
				$("#entityList").html(printEntities(data.entities));
				$(".removeEntity").on("click", function(event) {
						var entity = $(this).parent().children("div").text();
						console.log("removing "+entity+"...");
						api.entitySelections.removeEntityBySelectionID(selectionId, entity).
							done(function(data) {
									fire("modifiedEntityRemoved", {
										selectionId: selectionId,
										entity: entity,
										eventSource: event});
							});
				});
				fire("updateEntityListComplete", {selectionId: selectionId});
		});
	}
	function updateReferenceLinks(linkGroupId) {
		console.log("updateReferenceLinks("+linkGroupId+")");
		api.referenceLinks.getLinksByGroupID(linkGroupId).done(function(data) {
				$("#referenceLinks").html(printReferenceLinks(data.links));
				$(".removeReferenceLink").on("click", function(event) {
						var linkId = $(this).parent().attr("id");
						var domElement = $(this);
						api.referenceLinks.removeLinkByID(linkId).
							done(function(data) {
									$(event.target).parent().parent().fadeOut("slow");
									fire("modifiedLinkRemoved", {
										linkId: linkId,
										domElement: domElement});
							});
				});
		});
	}
	
	/*
	 * Initialization functions
	 *
	 * The first functions create GUI elements and also set up events.
	 * Afterwards, event listeners are registered, which are
	 * responsible for the functionality of the GUI such as reacting on
	 * changes to specific elements.
	 * Finally, the main initialization function puts everything together.
	 *
	 */
	 
	function initEntitySelectionChoice() {
		api.entitySelections.getEntitySelections().done(function(data) {
				that.entitySelections = EntitySelection.fromJSONArray(data.selections);
				$("#entitySelections").html(printEntitySelections(that.entitySelections));
				$("input:radio[name=entitySelection]").on("change", function() {
						fire("modifiedSelectionChosen", {
								selectionId: $(this).parent().children("input").attr("value"),
								name: $(this).parent().children(".entitySelectionName").text(),
								description: $(this).parent().children(".entitySelectionDescription").text(),
								domElement: $(this)});
				});
				$(".removeSelection").on("click", function(event) {
						fire("modifiedSelectionRemoved", {
								selectionId: $(this).parent().children("input").attr("value"),
								domElement: $(this)});
				});
		});
	}
	function initEntityList() {
		autocomplete($("#inputEntityList"))
			.on("keyup", function(event) {
				if(event.which==13) { // 'Return' key
					console.log("posting...");
					var entity = $(this).val();
					api.entitySelections.addEntityBySelectionID(
						that.getCurrentJob().getEntitySelectionID(), entity)
							.done(function(data) {
									fire("modifiedEntityAdded",{
											entity: entity,
											eventSource: event});
							});
				}
			}).on("keydown", function(event) {
				if(event.which==13) { // 'Return' key
					event.preventDefault();
				}
			});
	}
	function initDataSourceSelection() {
		function setRemoveClickListener() {
			$(".removeDataSource").on("click", function(event) {
					var dataSourceId = $(this).parent().children("input").attr("value");
					var domElement = $(this);
					console.log("dataSourceId="+dataSourceId);
					$.each(getDataSourceByID(dataSourceId), function(i, dataSource) {
							console.log("found dataSource="+dataSource);
							console.log("id="+dataSource.getID());
							fire("modifiedDataSourceRemoved", {
									dataSource: dataSource,
									domElement: domElement});
					});
			});
		}
		api.dataSources.getDataSources().done(function(data) {
				console.log("datasources retrieved!");
				that.dataSources = DataSource.fromJSONArray(data.dataSources);
				$("#dataSources").html(printDataSources(that.dataSources));
				$("input:radio[name=dataSourceSelection]").on("change", function() {
				fire("modifiedDataSourceChosen", {
						dataSourceId: $(this).parent().children("input").attr("value"),
						name: $(this).parent().children(".dataSourceName").text(),
						url: $(this).parent().children(".dataSourceURL").text(),
						domElement: $(this)});
				});
				setRemoveClickListener();
				fire("initDataSourceSelectionDone", this);
		});
		$("#buttonSubmitDataSource").on("click", function(data) {
				var dataSource = new DataSource(
						0, // data source ID set server-side
						$("#inputDataSourceName").val(),
						$("#inputDataSourceURL").val());
				console.log("submitting data source:"+
					dataSource.getName()+", "+dataSource.getURL());
				api.dataSources.addDataSource(dataSource).done(function(data) {
						dataSource.setID(data.dataSource.id);
						$("#dataSources form").append(
							printDataSource(
								dataSource.getID(),
								dataSource.getName(),
								dataSource.getURL()));
						setRemoveClickListener();
						fire("modifiedDataSourceAdded", { dataSource: dataSource });
					});
		});
	}
	function initReferenceLinkGroups() {
		api.referenceLinks.getLinkGroups().done(function(data) {
			that.linkGroups = ReferenceLinkGroup.fromJSONArray(data.groups);
			console.log("got "+that.linkGroups.length+" link groups");
			$("#referenceLinkGroups").html(printLinkGroups(that.linkGroups));
			$("input:radio[name=linkSelection]").on("change", function() {
					fire("modifiedLinkGroupSelection", {
							selectionId: $(this).attr("value"),
							name: $(this).parent().children(".linkGroupName").text(),
							description: $(this).parent().children(".linkGroupDescription").text(),
							domElement: $(this)});
			});
			$(".linkGroupName").on("input", function(event) {
					fire("modifiedLinkGroupName", {
							name: $(this).text(),
							linkGroupId: $(this).parent().children("input").attr("value")});
			});
			$(".linkGroupDescription").on("input", function(event) {
					fire("modifiedLinkGroupDescription", {
							description: $(this).text(),
							linkGroupId: $(this).parent().children("input").attr("value")});
			});
			$(".removeLinkGroup").on("click", function(event) {
					fire("modifiedLinkGroupRemoving", {
							domElement: $(this),
							linkGroupId: $(this).parent().children("input").attr("value")}); 
			});
			fire("initReferenceLinksEditorDone", this);
		});
	}
	function initRefrenceLinks() {
		function setRemoveClickListener() {
			$(".removeReferenceLink").on("click", function(event) {
					var linkId = $(this).parent().attr("id");
					var domElement = $(this);
					api.referenceLinks.removeLinkByID(linkId).done(function(data) {
							domElement.parent().fadeOut("slow");
							fire("modifiedLinkRemoved", { linkId: linkId });
					});
			});
		}
		autocomplete($("#inputLocalReferenceLink"))
			.on("keyup", function(event) {
				if(event.which==13) { // 'Return' key
					$("#inputRemoteReferenceLink").focus();
				}
			}).on("keydown", function(event) {
				if(event.which==13) { // 'Return' key
					event.preventDefault();
				}
			});
		$("#inputRemoteReferenceLink").on("keyup", function(event) {
			if(event.which==13) { // 'Return' key
				$.each(
					getReferenceLinkGroupByID(
						that.getCurrentJob().getReferenceLinkGroupID()), function(i, group) {
						console.log("adding link to group "+group.getID());
						var link = new ReferenceLink(
							0, // link ID assigned server-side
							$("#inputLocalReferenceLink").val(),
							$("#inputRemoteReferenceLink").val());
						api.referenceLinks.addLinkByGroupID(group.getID(), link)
							.done(function(data) {
								$("#referenceLinks").append(
									printReferenceLink(
										link.getID(),
										link.getURI0(),
										link.getURI1()));
								fire("modifiedLinkAdded", { link: link });
						});   
				});
			}
		}).on("keydown", function(event) {
			if(event.which==13) { // 'Return' key
				event.preventDefault();
			}
		});
	}
	function initJobs() {
		api.jobs.getJobs().done(function(data) {
				that.jobs = Job.fromJSONArray(data.jobs);
				$("#currentJobs").html(printJobs(that.jobs));
				$("input:radio[name=jobSelection]").on("change", function() {
						fire("modifiedJobChosen", {
									jobId: $(this).parent().children("input").attr("value"),
									name: $(this).parent().children(".jobName").text(),
									description: $(this).parent().children(".jobDescription").text(),
									domElement: $(this)});
				});
				$(".jobName").on("input", function() {
						fire("modifiedJobName", {
								name: $(this).text(),
								jobId: $(this).parent().children("input").attr("value")});
				});
				$(".jobDescription").on("input", function() {
						fire("modifiedJobDescription", {
								description: $(this).text(),
								jobId: $(this).parent().children("input").attr("value")});
				});
				$(".removeJob").on("click", function() {
						fire("modifiedJobRemoving", {
								domElement: $(this),
								jobId: $(this).parent().children("input").attr("value")});
				});
				mw.loader.using('jquery.ui.progressbar', function () {
					$(".jobProgress").progressbar({
						value: 50,
						change: function() {
							$(".progress-label").text( progressbar.progressbar( "value" ) + "%" );},
						complete: function() {
							$(".progress-label").text( "Complete!" );}
					});
				});
				fire("initJobsDone");
		});
	}
	function initReview() {
		// displaying a result depends on a selected job, so this
		// functionality is implemented below within the
		// initChangeListeners() function.
		// apart from that there is currently nothing else to initialize, yet
		// but we keep this for easy maintenance and changeability
	}
	/*
	 * This function registers many listeners which handle GUI interactions.
	 * They cannot be removed by users of SMWClient instances, as this would
	 * require an instance of the corresponding function. This is also intended,
	 * as they are required for the correct function of an SMWClient instance.
	 */
	function initChangeListeners() {
		that.on("modifiedSelectionChosen", function(context) {
				if(context.selectionId  == "new") { // create a new selection
					console.log("creating new selection name="+context.name+", "+
						context.description);
					var selection = new EntitySelection(
							0, // ID, will be assigned by the server
							context.name,
							context.description);
					api.entitySelections.addEntitySelection(selection)
						.done(function(data) {
								selection.setID(data.selection.id);
								if(typeof that.entitySelections == "undefined") {
									that.entitySelections = [];
								}
								that.entitySelections.push(selection);
								fire("modifiedSelectionAdded", { entitySelection: selection });
						});
					$(referenceLinks).html("There are no reference links in this selection yet...");
				} else { // reuse an existing selection
					updateEntities(context.selectionId);
					if(typeof that.getCurrentJob() != "undefined") {
						that.getCurrentJob().setEntitySelectionID(context.selectionId);
						fire("modifiedCurrentJob");
					}
				}
		});
		that.on("modifiedSelectionAdded", function(context) {
				if(typeof that.getCurrentJob() != "undefined") {
					that.getCurrentJob().setEntitySelectionID(context.selection.getID());
					fire("modifiedCurrentJob");
				}
		});
		that.on("modifiedSelectionRemoved", function(context) {
				api.entitySelections.removeEntitySelectionByID(context.selectionId);
				context.domElement.parent().fadeOut("slow");
		});
		that.on("modifiedDataSourceChosen", function(context) {
				if(typeof that.getCurrentJob() != "undefined") {
					that.getCurrentJob().setDataSourceID(context.dataSourceId);
					api.jobs.updateJob(that.getCurrentJob()).done(function(data) {
							fire("modifiedCurrentJob");
					});
				}
		});
		that.on("modifiedDataSourceRemoved", function(context) {
				api.dataSources.removeDataSource(context.dataSource);
				context.domElement.parent().fadeOut("slow");
		});
		that.on("modifiedEntityAdded", function(context) {
				$("#entityList").append(printEntity(context.entity));
				console.log("eventSource.target="+context.eventSource.target);
				$(context.eventSource.target).val("");
		});
		that.on("modifiedEntityRemoved", function(context) {
				$(context.eventSource.target).parent().fadeOut("slow");
		});
		that.on("modifiedLinkGroupSelection", function(context) {
				$.each(getReferenceLinkGroupByID(context.selectionId),
					function(i, group) {
						console.log("link group "+group.getName()+" has been set");
						that.currentLinkGroup = group;
				});
				if(context.selectionId == "new") { // create new link group
					console.log("creating new link group "+context.name);
					var linkGroup = new ReferenceLinkGroup(
						0, // ID set server-side
						context.name,
						context.description);
					api.referenceLinks.addLinkGroup(linkGroup).done(function(data) {
							linkGroup.setID(data.group.id);
							if(typeof that.linkGroups == "undefined") {
								that.linkGroups = [];
							}
							that.linkGroups.push(linkGroup);
							fire("modifiedLinkGroupAdded", { linkGroup: linkGroup });
					});
				} else { // reuse existing link group
					updateReferenceLinks(context.selectionId);
				}
				if(typeof that.getCurrentJob() != "undefined") {
					that.getCurrentJob().setReferenceLinkGroupID(context.selectionId);
					fire("modifiedCurrentJob");
				}
		});
		that.on("modifiedLinkGroupRemoving", function(context) {
				$.each(getReferenceLinkGroupByID(context.linkGroupId), function(i, group) {
						api.referenceLinks.removeLinkGroupByID(context.linkGroupId)
							.done(function(data) {
									removeReferenceLinkGroupByID(context.linkGroupId);
									context.domElement.parent().fadeOut("slow");
							});
				});
		});
		that.on("initJobsDone", function(context) {
				console.log("registering job start listener...");
				$("#buttonStartNewJob").on("click", function(event) {
						var jobId = $(event.target).parent().
								children("input[name=jobSelection]").attr("value");
						console.log("trying to start job "+jobId);
						$.each(getJobByID(jobId), function(i, job) {
								console.log("starting...");
								api.jobs.startJob(job).done(function(data) {
									alert(data.result.request);
							});
						});
				});
		});
		that.on("modifiedJobChosen", function(context) {
				function initReview(jobId) { // loads links from the server and prints them
					api.matchingResults.getLinksByJobID(jobId).done(function(data) {
							that.results = Cell.fromJSONArray(data.links);
							$("#reviewLinks").html(printResultLinks(that.results));
							$(".removeResultLink").on("click", function() {
									fire("modifiedResultLinkRemoving", {
											linkId: $(this).parent().attr("id"),
											domElement: $(this)});
							});
							$("#buttonPublishResult").toggle();
							$("#buttonPublishResult").on("click", function() {
									fire("modifiedResultPublishing");
							});
							mw.loader.using( 'jquery.tipsy', function () {
									// hard to display ajax content with tipsy
							});
							fire("initResultViewDone");
					});
				}
				if(context.jobId=="new") { // create a new job
					console.log("creating new job...");
					console.log("job name="+context.name);
					var job = new Job(
						0, // job ID set server-side
						context.name,
						context.description,
						null, // entity selection ID, set below (may be undefined)
						null, // reference link group ID, same here
						null); // data source ID, same here
					if(typeof that.currentSelection != "undefined") {
						job.setEntitySelectionID(that.currentSelection.getID());
					}
					if(typeof that.currentLinkGroup != "undefined") {
						job.setReferenceLinkGroupID(that.currentLinkGroup.getID());
					}
					if(typeof that.currentDataSource != "undefined") {
						job.setDataSourceID(that.currentDataSource.getID());
					}
					api.jobs.addJob(job).done(function(data) {
							job.setID(data.job.id);
							that.setCurrentJob(job);
							initReview(job.getID());
					});
				} else { // reuse existing job
					$.each(getJobByID(context.jobId), function(i, job) {
							that.setCurrentJob(job);
							initReview(job.getID());
					});
				}
		});
		that.on("modifiedJobName", function(context) {
				$.each(getJobByID(context.jobId), function(i, job) {
					job.setName(context.name);
					api.jobs.updateJob(job).
						done(function(data) {
								fire("modifiedJob", {jobId: context.jobId});
						});
				});
		});
		that.on("modifiedJobDescription", function(context) {
				$.each(getJobByID(context.jobId), function(i, job) {
					job.setDescription(context.description);
					api.jobs.updateJob(job).
						done(function(data) {
								fire("modifiedJob", {jobId: context.jobId});
						});
				});
		});
		that.on("modifiedJobRemoving", function(context) {
				$.each(getJobByID(context.jobId), function(i, job) {
						api.jobs.removeJobByID(job.getID()).done(function(data) {
								var jobId = job.getID();
								removeJobByID(jobId);
								context.domElement.parent().fadeOut("slow");
								fire("modifiedJobRemoved", { jobId: jobId });
						});
				});
		});
		that.on("modifiedDataSourceAdded", function(context) {
				that.dataSources.push(context.dataSource);
		});
		that.on("modifiedLinkGroupName", function(context) {
				
		});
		that.on("modifiedLinkGroupDescription", function(context) {
				
		});
		that.on("modifiedResultLinkRemoving", function(context) {
				api.matchingResults.removeLinkByID(context.linkId).done(function(data) {
						// TODO: remove link from cached results
						context.domElement.parent().fadeOut("slow");
						fire("modifiedResultLinkRemoved", { linkId: context.linkId });
				});
		});
		that.on("modifiedResultPublishing", function(context) {
				api.matchingResults.publishByJobID(that.getCurrentJob().getID())
					.done(function(data) {
							alert(data.result.operation);
					});
		});
		that.on("modifiedCurrentJob", function(context) {
				api.jobs.updateJob(that.getCurrentJob());
		});
	}
	function initTabs() {
		mw.loader.using("jquery.ui.tabs", function() {
				$("#tabs").tabs();
		});
		fire("initTabsDone", this);
	}
	function setInputChecked(cssClass, id) {
		$('.'+cssClass+' > input[value|="'+id+'"]').attr("checked", "true");
	}
	function semanticWebBrowser(article) {
		return $.ajax({
			// TODO: get url dynamically
			url : "/smw-cora/index.php/Spezial:Durchsuchen?&article="+article,
			type : "GET",
			async : false
		}).done(function(data) {
			var nodes = $.parseHTML(data);
			var found = $(nodes).find("#mw-content-text");
			console.log("found="+found.html());
			return "shit";
		});
	}
	// priviledged methods, able to access private methods and
	// also accessible from the outside like public methods
	this.init = function() {
		console.log("initializing client...");
		initJobs();
		initEntitySelectionChoice();
		initEntityList();
		initDataSourceSelection();
		initReferenceLinkGroups();
		initRefrenceLinks();
		initReview();
		initChangeListeners();
		initTabs();
		fire("initAllDone", this);
		return this;
	};
	this.getCurrentJob = function() {
		return this.currentJob;
	};
	this.setCurrentJob = function(job) {
		this.currentJob = job;
		$.each(getEntitySelectionByID(job.getEntitySelectionID()),
			function(i, selection) {
				console.log("setting currentSeletion to "+selection);
				this.currentSelection = selection;
				console.log("currentSelection with ID: "+this.currentSelection.getID());
		});
		$.each(getDataSourceByID(job.getDataSourceID()),
			function(i, dataSource) {
				this.currentDataSource = dataSource;
		});
		$.each(getReferenceLinkGroupByID(job.getReferenceLinkGroupID()),
			function(i, linkGroup) {
				console.log("setting current link group to ID "+linkGroup.getID());
				this.currentLinkGroup = linkGroup;
		});
		// apply the selections in the GUI
		setInputChecked("entitySelection", job.getEntitySelectionID());
		setInputChecked("dataSources", job.getDataSourceID());
		setInputChecked("linkGroups", job.getReferenceLinkGroupID());
		// update the list of entities in this job's selection and
		// update the list of reference links as well
		updateEntities(job.getEntitySelectionID());
		updateReferenceLinks(job.getReferenceLinkGroupID());
	};
}

// wait for the DOM to be loaded
$(function() {
		// main logic
		var api = new SMWEnrichApi();
		var client = new SMWEnrichClient(api);
		client.on("initAllDone", function(context) {
				console.log("init done!");
		});
		client.init();
		client.on("modifiedCurrentJob", function(context) {
				console.log("current job has been changed");
		});
});

