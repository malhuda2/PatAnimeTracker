  create table users (
  id integer primary key auto_increment,
  username varchar(45) not null,
  password varchar(45) not null
);

create table series (
id integer not null PRIMARY KEY AUTO_INCREMENT,
stitle varchar(200) not null,
newday integer default -1,
 lastcheck timestamp
);


create table userseries (
 userid integer not null,
 seriesid integer not null,
 latest_ep integer not null
);
create index userseries_usersid on userseries(userid);

create table urlseries (
 seriesid integer not null,
 episode integer not null,
 url varchar(255)
);
create index urlseries_seriesid on urlseries(seriesid);