ALTER TABLE users add country smallint(6) AFTER city;
ALTER TABLE users ADD FOREIGN KEY (country) REFERENCES countries(id);
