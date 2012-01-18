CREATE TABLE latlngs
(id INT(11),
 tripid INT(11),
 lat DECIMAL(10, 8),
 lng DECIMAL(11, 8),
 PRIMARY KEY (id),
 FOREIGN KEY (tripid) references trips(id)) ENGINE=InnoDB;
