create table searches(searchid smallint(6) not null auto_increment, userid bigint(20) not null, FOREIGN KEY (userid) REFERENCES users(userid), PRIMARY KEY  (searchid)) type=InnoDB;
create table searchterms(searchid smallint(6) not null, searchquery VARCHAR(2048), FOREIGN KEY (searchid) REFERENCES searches(searchid)) type=InnoDB;
