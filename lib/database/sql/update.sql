alter table cms_app drop uid;
alter table cms_app drop public;
alter table cms_app drop ctime;
alter table cms_app drop status;
alter table cms_app drop title;
alter table cms_app drop installdir;

alter table cms_app add  appversion varchar(64) not null;
alter table cms_app drop remote_version;
alter table cms_app drop remote_download_url;
alter table cms_app drop install;
alter table cms_app drop platform;


alter table cms_file change uses used int null default 0;
alter table cms_file add shared int null default 0;
alter table cms_file add lsize bigint not null;
alter table cms_file add target_id int not null;
alter table cms_file DROP INDEX path;


-- #fixed for app 
alter table cms_app add peeravid varchar(64) not null;
alter table cms_app add nextavid varchar(64) not null;

-- #fixed   
alter table cms_file2model add fieldname varchar(64) null;

--#fixed 
alter table cms_server add web_prefix varchar(128) null;


-- #fixed 0.11
create table cms_pubto(
  id int NOT NULL,
  modname varchar(64) NOT NULL,
  mid int NOT NULL,  
  to_modname varchar(64) NOT NULL,
  to_mid int NOT NULL,  
  target_id varchar(128) NOT NULL,  
  unique key(modname, mid, to_modname, to_mid),
  unique key(target_id),
  primary key(id)
);
alter table cms_app change column avid uuid varchar(64) not null;

-- #fixed 0.11 for cms_storage
alter table cms_storage change column stype type int NOT NULL;
alter table cms_storage change column spath path varchar(128) NULL;
alter table cms_storage drop webpath;
alter table cms_storage add oid int not null;
alter table cms_storage add npath varchar(128) NULL;
alter table cms_storage add nmountdir varchar(256) NOT NULL;
alter table cms_storage add ctime bigint not null;
alter table cms_storage add ts bigint not null;
alter table cms_storage add master int not null;
alter table cms_storage add auth int not null;
update cms_storage set status=1 where id=1;

alter table cms_app drop bean;


create table cms_module(
  id int NOT NULL,
  name varchar(64) not null,
  title varchar(64) not null,
  type varchar(64) not null,
  description text null,
  url varchar(256) NULL,
  src varchar(256) null,
  content text null,
  mid varchar(64) not null,
  ctype int not null,
  unique key(mid),
  primary key(id)
);

create table cms_module2tplfile(
  id int NOT NULL,
  mid int NOT NULL,
  tplfile varchar(256) null,  
  UNIQUE KEY(mid,tplfile),
  primary key(id)
);


create table cms_module_params(
  id int NOT NULL,
  mid int NOT NULL,
  cid int NULL,
  flags int null,
  maxnum int null,
  num int null,
  tags varchar(32) null,
  UNIQUE KEY(mid),
  primary key(id)
);

-- #fixed for 7to11
alter table cms_session add ts bigint NOT NULL;

alter table cms_file drop published;
alter table cms_file add reserved text null;
alter table cms_file add duration double null;
alter table cms_file add bitrate double null;
-- #fixed for 0.11
alter table cms_group change column description description text NULL;

alter table cms_catalog add type int NOT NULL;
alter table cms_catalog add style int NULL;
alter table cms_catalog add modname varchar(64) NULL;
alter table cms_catalog add mid int NULL;

-- #fixed 20260402
alter table cms_pubto add ts bigint not null;
alter table cms_pubto add status int not null;
alter table cms_pubto change column target_id tuuid varchar(128) NOT NULL;

alter table cms_file2org add used int not null;
alter table cms_file2org add pid int not null;
alter table cms_file2org DROP INDEX fid;
alter table cms_file2org DROP INDEX oid;
alter table cms_file2org ADD UNIQUE (fid,sid);
alter table cms_file2org change column last_ts ts bigint NOT NULL;


-- #fixed 20260409

alter table cms_msg drop num;
alter table cms_msg add dtype int null;
alter table cms_msg add unum int not null;
alter table cms_msg add enum int not null;
alter table cms_msg change column opened opens int not null;
alter table cms_msg change column dtype dtype int null;

create table cms_pub(
  id int NOT NULL,
  uuid varchar(128) NOT NULL,
  modname varchar(64) NOT NULL,
  mid int NOT NULL,  
  tomod text NULL,
  flags int not null,
  etype int not null,
  etime bigint null,
  eflags int not null,
  ts bigint NOT NULL,
  status int not null,
  unique key(modname, mid),
  unique key(uuid),
  primary key(id)
);


-- #fixed 20260409
alter table cms_pubto change column target_id tuuid varchar(128) NOT NULL;

-- #fixed 20260418
alter table cms_org add uuid varchar(64) not null;
alter table cms_media_platform add uuid varchar(64) not null;

-- #fixed 20260503
alter table cms_app DROP INDEX name;
-- #fixed app
alter table cms_app add vtype int not null;
alter table cms_app change column appid uuid varchar(64) NULL;
alter table cms_app add path varchar(512) null;

-- #fixed 20260504
alter table cms_pub add flags int not null;

