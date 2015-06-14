CREATE TABLE smw_enrich_data_sources (
	data_source_id INT NOT NULL PRIMARY KEY,
	data_source_name VARCHAR(100) NOT NULL,
	data_source_url VARCHAR(2000) NOT NULL
);
CREATE INDEX idx_smw_enrich_data_source_names ON smw_enrich_data_sources(data_source_name);
CREATE TABLE smw_enrich_entity_selections_meta (
	selection_id int NOT NULL PRIMARY KEY,
	selection_name VARCHAR(100) NOT NULL,
	selection_description TEXT
);
CREATE INDEX idx_smw_enrich_entity_selection_names ON smw_enrich_entity_selections_meta(selection_name);
CREATE TABLE smw_enrich_entity_selections (
	entity_id VARCHAR(2000) NOT NULL, -- URI of an SMW entity
	selection_id INT NOT NULL, -- allows reusing entity selections
	FOREIGN KEY(selection_id) REFERENCES smw_enrich_entity_selections_meta(selection_id)
	-- MySQL defaul max key length limitation: 'max key length is 767 bytes'
	-- CONSTRAINT unique_entities_per_selection UNIQUE(entity_id, selection_id)
);
CREATE TABLE smw_enrich_reference_links_meta (
	link_group_id INT NOT NULL PRIMARY KEY,
	link_group_name VARCHAR(100) NOT NULL,
	link_group_description VARCHAR(2000) NOT NULL
);
CREATE INDEX idx_smw_enrich_referrence_link_group_names ON smw_enrich_reference_links_meta(link_group_name);
CREATE TABLE smw_enrich_reference_links (
	link_id int NOT NULL PRIMARY KEY,
	local_entity_id VARCHAR(2000) NOT NULL,
	external_entity_id VARCHAR(2000) NOT NULL,
	link_group_id INT NOT NULL,
	FOREIGN KEY(link_group_id) REFERENCES smw_enrich_reference_links_meta(link_group_id)
	-- MySQL key length limitation (see above)
	-- CONSTRAINT unique_reference_links_per_group UNIQUE(link_group_id, local_entity_id, external_entity_id)
);
CREATE TABLE smw_enrich_jobs (
	job_id int NOT NULL PRIMARY KEY,
	job_name VARCHAR(100) NOT NULL,
	job_description TEXT,
	job_start_date INT,
	job_finish_date INT,
	job_progress FLOAT,
	selection_id INT,
	link_group_id INT,
	data_source_id INT,
	FOREIGN KEY(selection_id) REFERENCES smw_enrich_entity_selections_meta(selection_id),
	FOREIGN KEY(link_group_id) REFERENCES smw_enrich_reference_links_meta(link_group_id),
	FOREIGN KEY(data_source_id) REFERENCES smw_enrich_data_sources(data_source_id)
);
CREATE INDEX idx_smw_enrich_job_names ON smw_enrich_jobs(job_name);
CREATE TABLE smw_enrich_results (
	link_id int NOT NULL PRIMARY KEY,
	local_entity_id VARCHAR(2000) NOT NULL,
	external_entity_id VARCHAR(2000) NOT NULL,
	link_relation VARCHAR(100) NOT NULL,
	link_measure FLOAT NOT NULL,
	job_id INT NOT NULL,
	FOREIGN KEY(job_id) REFERENCES smw_enrich_jobs(job_id)
	-- MySQL key length limitation (see above)
	-- CONSTRAINT unique_links_per_result UNIQUE(local_entity_id, external_entity_id)
);
