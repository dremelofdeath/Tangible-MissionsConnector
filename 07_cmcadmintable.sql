create table cmcadmins(id smallint(6) not null auto_increment, userid bigint(20) not null, FOREIGN KEY (userid) REFERENCES users(userid), PRIMARY KEY  (id)) type=InnoDB;
