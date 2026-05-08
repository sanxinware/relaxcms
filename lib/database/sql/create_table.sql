
-- create_table.sql

create table cms_user (
  id int NOT NULL,
  uid int NOT NULL comment 'UID',
  name varchar(64) NOT NULL comment '用户名',
  description text NULL,  
  password varchar(32) NOT NULL,
  nickname varchar(32) null comment '昵称',
  email varchar(64) NULL,
  avatar varchar(256) NULL,
  rid int not NULL,
  oid int not NULL,
  flags int not NULL,
  type int not null, 
  last_time bigint null,
  last_ip varchar(32) null,
  fails int not null default 0,
  logins int not null default 0,
  ts bigint not null,
  pwd_last_update_ts int not null,
  last_pwd text null,
  allow_ip text null,
  token text null,
  status int not null,
  UNIQUE(uid),
  UNIQUE(name),
  primary key(id)
);


create table cms_account (
  id int NOT NULL,
  uid int NOT NULL comment 'UID',
  account varchar(32) NOT NULL comment '帐号',
  type int not null, 
  status int not null, 
  UNIQUE(account),
  primary key(id)
);

create table cms_user_seccode (
  id int NOT NULL,
  secid	varchar(64)	not null,
  seccode	varchar(64)	not null,
  ts	bigint	not null,
  ttl	int	not null, 
  UNIQUE KEY (secid), 
  primary key(id)
);


create table cms_user_token (
  id int NOT NULL,
  uid int NOT NULL,
  token varchar(64) NOT NULL,
  secret varchar(256) NOT NULL,
  expired bigint NOT NULL,
  UNIQUE KEY (uid), 
  UNIQUE KEY (token), 
  primary key(id)
);

-- 用户会话表
create table cms_session (
    id int not null,
    ssid varchar(64) not null,
    uid int not null,
    model varchar(32) not null,
    cktime int not null default 0,
    login_ip varchar(40) null,
    login_type int null,
    login_ts bigint NOT NULL,
    expire_ts bigint NOT NULL,
    ts bigint NOT NULL,
    hkey varchar(32) not null,    
    UNIQUE KEY (ssid), 
	primary key(id)
);

create table  cms_group(
  id int NOT NULL,
  name varchar(64) NOT NULL,
  description text NULL,
  type tinyint NOT NULL,
  UNIQUE KEY(name),
  primary key(id)
);


create table cms_privilege(
 id int NOT NULL,
 name varchar(64) not null,
 description varchar(256) not null,
 component varchar(32) not null,
 pid int NOT NULL,
 UNIQUE KEY(component),
 primary KEY(id)
);

create table cms_privilege2group(
 id int not null,
 pid int NOT NULL,
 gid int not null,
 permision int default 7,
 UNIQUE key(pid,gid),
 primary KEY(id)
);

create table cms_role(
  id int NOT NULL,
  name varchar(64) NOT NULL,
  description varchar(256) NULL,
  type int NOT NULL,
  level int NOT NULL,
  UNIQUE KEY(name),
  primary key(id)
);

create table cms_group2role(
 id int not null,
 gid int NOT NULL,
 rid int not null,
 UNIQUE KEY(gid,rid),
 primary key(id) 
);

create table cms_org (
  id int NOT NULL,
  name varchar(64) NOT NULL,
  description text NULL,
  telephone varchar(64) NULL,
  email varchar(64) NULL,
  address varchar(256) NULL,
  uids text NULL,
  pid int not null, 
  status int not null, 
  primary key(id)
);



create table cms_log (
	id int not null,
	ts bigint not null,
	ip varchar(40) NOT NULL,
	description text null,
	errno int not null,
	uid int not null,
	status int not null,
	modname varchar(32) null,
	mid int null,
	action varchar(32) null,
	level int null default 7,
	oldobj text null,
	newobj text null,
	primary key(id)
);


-- var
create table cms_var (
  id int NOT NULL,
  name varchar(32) not null default '',
  value varchar(128) not null default '',
  title varchar(128) null,
  attr int null,
  taxis tinyint null,
  pid int NOT NULL default 0,
  PRIMARY KEY (id)
);


create table cms_server(
  id int NOT NULL,
  name varchar(32) NOT NULL,
  description text NULL,
  spid varchar(64) not null,
  ip varchar(40) not null,
  os varchar(256) null,
  version varchar(32) null,
  oid int not null,
  web_prefix varchar(128) null,
  rtmp_prefix varchar(128) null,
  lan_rtmp_prefix varchar(128) null,
  live_prefix varchar(128) null,
  lan_live_prefix varchar(128) null,
  vod_prefix varchar(128) null,
  lan_vod_prefix varchar(128) null,
  download_prefix varchar(128) null,
  lan_download_prefix varchar(128) null,
  ts bigint not null,
  mode int not null default 0,
  online int not null default 0,
  status int not null default 0,
  unique key(spid),
  primary key(id)
) ;

-- storage
create table cms_storage (
  id int NOT NULL,
  name varchar(64) NOT NULL,
  description text null,
  type int NOT NULL,  
  suid varchar(40) not null,
  oid int not null,
  sid int not null,  
  path varchar(128) NULL,
  mountdir varchar(256) NOT NULL,
  auth int not null,
  username varchar(32) NULL,
  password varchar(32) NULL,
  total bigint NOT NULL,
  used bigint not null,  
  npath varchar(128) NULL,
  nmountdir varchar(256) NOT NULL,
  vod_prefix varchar(128) null,
  download_prefix varchar(128) null,
  lan_vod_prefix varchar(128) null,
  lan_download_prefix varchar(128) null,  
  ctime bigint not null,
  ts bigint not null,
  master int NOT NULL,
  status int NOT NULL,
  UNIQUE KEY(name),
  UNIQUE KEY(suid),
  UNIQUE KEY(path),
  primary key(id)
);

-- cms_storage_dispatch
create table cms_storage_dispatch (
	id int not null,
	uid int not null,	
	total bigint not null,
	used bigint not null,
	unique key(uid),
	primary key(id)
);

-- file
create table cms_file (
  id int NOT NULL,
  name varchar(256) NOT NULL,
  filename varchar(256) NULL,
  fileid varchar(40) NOT NULL,
  md5id varchar(40) NULL,
  path varchar(256) NULL,
  extname varchar(16) NOT NULL,
  type int NOT NULL,
  size bigint NOT NULL,
  lsize bigint NOT NULL,
  description text null,
  reserved text null,
  fromurl varchar(1024) null,
  width int null default 0,
  height int null default 0,
  duration double null,
  bitrate double null,
  hits int null default 0,  
  gid int not null default 0,
  oid int not null default 0,
  sid  int  not null,
  cuid int not null,
  ctime bigint  NULL,
  uid int not null,
  ts int not null,
  is_default int not null default 0,
  isdir int not null default 0,
  taxis int NOT NULL default 0,
  used int null default 0,
  shared int null default 0,
  pid int not null default 0,
  target_id int not null default 0,
  convert_id int not null default 0,
  snap_id int not null default 0,  
  flags int null default 0,
  status int not null default 0,
  INDEX(type),
  INDEX(md5id),
  UNIQUE KEY(pid,filename),
  UNIQUE KEY(fileid),
  primary key(id)
);

create table if not exists cms_file2storage (
  id int NOT NULL,
  fid int NOT NULL,
  sid int NOT NULL,
  oid int NOT NULL,
  status int not null, 
  lsize bigint not null,
  ts bigint not null,
  UNIQUE KEY(fid,sid),
  primary key(id)
);


create table cms_file2model (
  id int NOT NULL,
  fid int NOT NULL,
  modname varchar(64) null,
  fieldname varchar(64) null,
  mid int not null default 0,
  title	varchar(128)	NULL,
  description	text	NULL,
  taxis	int	NULL,
  linkurl	varchar(256)	NULL,
  num int not null default 0,
  checked int not null default 0,
  UNIQUE KEY(fid,modname,mid,fieldname),
  primary key(id)
);


-- app
create table cms_app(
 id int not null,
 name varchar(64) not null,
 description text null,
 type int NOT NULL,
 version varchar(64) not null,
 path varchar(512) null,
 uuid varchar(64) not null,
 vtype int not null,
 logo varchar(256) NULL,
 developer varchar(64)	NULL,
 language int NOT NULL,
 url varchar(256) NULL,
 bean int NOT NULL,
 embeded int not null,
 uninstall int not null,
 local int not null,
 remote int not null,
 appname varchar(64) not null,
 appversion varchar(64) not null,
 peeravid varchar(64) not null,
 nextavid varchar(64) not null,
 rkey int NOT NULL,
 copyright text null,
 installed int not null,
 ts	bigint NOT NULL,
 UNIQUE (uuid),
 PRIMARY KEY (id)
);


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


create table cms_pubto(
  id int NOT NULL,
  modname varchar(64) NOT NULL,
  mid int NOT NULL, 
  uuid varchar(64) not null, 
  to_modname varchar(64) NOT NULL,
  to_mid int NOT NULL,  
  to_uuid varchar(64) not null,
  tuuid varchar(128) NOT NULL,
  ts bigint NOT NULL,
  status int not null,
  unique key(modname, mid, to_modname, to_mid),
  unique key(uuid, to_uuid),
  unique key(tuuid),
  primary key(id)
);



-- 内容表 cms_content
create table cms_content(
  id int NOT NULL,
  uuid varchar(64) not null,
  name varchar(128) NOT NULL,
  description text null,
  cid int NOT NULL,
  flags int not null,
  icon varchar(255) NULL,
  photo varchar(255) NULL,
  video varchar(255) NULL,
  link varchar(255) NULL,
  refer varchar(64) NULL,
  author varchar(64) null,
  content text null,
  aids text null,
  hits int NOT NULL default 0,
  cmts int NOT NULL default 0,
  taxis int not null,  
  tpl_content varchar(32) null,
  cuid int not null,
  ctime bigint not null,
  oid int not null,
  uid int not null,
  ts bigint NOT NULL,  
  status int not null,
  INDEX (status),  
  INDEX (taxis),  
  INDEX (ts),  
  UNIQUE KEY(uuid), 
  primary key(id)
);

-- 目录表 cms_catalog
create table cms_catalog(
  id int NOT NULL,
  uuid varchar(64) not null,
  name varchar(64) NOT NULL,
  description text NULL,
  pid int NOT NULL,
  type int NOT NULL,
  modname varchar(64) NULL,
  mid int NULL,
  link varchar(256) NULL,
  target varchar(64) NULL,
  flags int not null,
  style int NULL,
  viewmode int NULL,
  viewtype int NULL,
  icon varchar(64) NULL,
  photo varchar(256) null,
  taxis int NULL default 0,
  tpl_list_root varchar(32) null,
  tpl_list varchar(32) null,
  tpl_content_root varchar(32) null,
  tpl_content varchar(32) null,
  class varchar(64) NULL,
  metakeyword varchar(256) NULL,
  metadescrip varchar(256) NULL,
  depth int NULL default 0,
  cuid int not null,
  ctime bigint not null,
  oid int not null,
  uid int not null,
  ts bigint NOT NULL,  
  status int NOT NULL,
  INDEX (status),  
  primary key(id)
);

create table if not exists cms_content2model(
  id int not null,
  cid int not null,
  modname varchar(32) null,
  mid int not null,
  primary key(id)
);
