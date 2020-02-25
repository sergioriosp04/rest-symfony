CREATE DATABASE api_symfony;
USE api_symfony;

CREATE TABLE users (
    id      int(255) auto_increment not null ,
    name    varchar(100) not null,
    surnamen varchar(100),
    email   varchar(255) not null,
    password   varchar(255) not null,
    role    varchar(50),
    created_at datetime DEFAULT current_timestamp ,
    CONSTRAINT pk_users PRIMARY KEY(id),
    CONSTRAINT uq_email UNIQUE(email)
);

INSERT INTO users VALUES (NULL, "sergio", "rios", "sergioriosp04@gmail.com", "1036400564", "admin", current_time);

CREATE TABLE videos (
    id      int(255) auto_increment not null ,
    user_id int(255) not null,
    title    varchar(255) not null,
    description text,
    url   varchar(255) not null,
    status    varchar(50),
    created_at datetime DEFAULT current_timestamp ,
    updated_at datetime DEFAULT current_timestamp ,
    CONSTRAINT pk_videos PRIMARY KEY(id),
    CONSTRAINT fk_videos_users FOREIGN KEY(user_id) REFERENCES users(id)
);

INSERT INTO videos VALUES(null, 1, "otra cancion 2", "descripcion del video", "https://www.youtube.com/watch?v=JwsgCnBLL4A","", current_time, current_time );