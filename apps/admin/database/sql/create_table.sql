
-- create_table.sql

create table cms_history (
	id int not null,
	uid int not null,
	cname varchar(64) NOT NULL,
	tname varchar(64) NOT NULL,
	ts bigint not null,
	primary key(id)
);
	

