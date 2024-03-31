DROP DATABASE IF EXISTS calender;
CREATE DATABASE calendar DEFAULT CHARACTER set utf8 COLLATE utf8_general_ci;
DROP USER'cca'@'localhost';
CREATE USER'cca'@'%' IDENTIFIED BY 'password';
USE calendar;
GRANT ALL ON calendar.* to 'cca'@'%';
DROP TABLE IF EXISTS events;
CREATE TABLE events (
title VARCHAR(100) not null,
date VARCHAR(20) not null primary key,
time VARCHAR(30) not null,
category VARCHAR(100) not null,
detail VARCHAR(400) not null
);
INSERT INTO events VALUES(
    'test_title',
    '2024-03-11',
    '15:00',
    'test',
    'test_data'
);
INSERT INTO events VALUES(
    'insert_test',
    '2024-03-12',
    '14:00',
    'insert',
    'insert_test_data'
);
INSERT INTO events VALUES(
    'insert_test',
    '2034-02-03',
    '11:00',
    'insert',
    'insert_test_data'
);
select * from events where date  like '_____03___';
select * from events where date ;
SELECT date FROM events WHERE date like '2024-03-05';
UPDATE events SET title='SQL変更テスト', date='2024-03-05', time='23:00',category='プライベート',detail='SQL変更のテスト' WHERE date='2024-03-05';
DELETE FROM events WHERE date='2024-02-06';