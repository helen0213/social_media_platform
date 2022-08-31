CREATE TABLE Users(
	username char(50) PRIMARY KEY,
	password char(50) NOT NULL
);

CREATE TABLE Chats(
	cname char(50) PRIMARY KEY
);

CREATE TABLE Messages0(
	cname char(50),
	senderName char(50),
      receiverName char(50) NOT NULL,
	PRIMARY KEY(cname, senderName),
	FOREIGN KEY(cname) REFERENCES Chats 
            ON DELETE CASCADE
);

CREATE TABLE Messages1(
	cname char(50),
	time timestamp,
	mtext char(80) NOT NULL,
	senderName char(50) NOT NULL,
	PRIMARY KEY(cname, time),
      FOREIGN KEY(cname, senderName) REFERENCES Messages0
            ON DELETE CASCADE
);

CREATE TABLE Has_Chats(
	username char(50),
	cname char(50),
	PRIMARY KEY(username, cname),
	FOREIGN KEY(username) REFERENCES Users 
            ON DELETE CASCADE,
    FOREIGN KEY(cname) REFERENCES Chats 
            ON DELETE CASCADE
);

CREATE TABLE Topics(
	tname char(50) PRIMARY KEY,
	numberOfPost int NOT NULL
);

CREATE TABLE Creates_Topics(
	tname char(50) PRIMARY KEY,
	username char(50),
	FOREIGN KEY(tname) REFERENCES Topics
		ON DELETE CASCADE,
	FOREIGN KEY(username) REFERENCES Users
		ON DELETE CASCADE
);

CREATE TABLE Sponsors(
	sid char(50) PRIMARY KEY,
    sname char(50) NOT NULL UNIQUE,
	password char(50) NOT NULL
);

CREATE TABLE Creates_Ads0(
	cost int PRIMARY KEY,
	displayTimeInDays int NOT NULL UNIQUE
);

CREATE TABLE Creates_Ads1(
	sid char(50),
	title char(50),
	cost int NOT NULL,
	description char(80) NOT NULL,
    timeCreated timestamp NOT NULL,
	PRIMARY KEY(sid,title),
	FOREIGN KEY(sid) REFERENCES Sponsors 
            ON DELETE CASCADE,
    FOREIGN KEY(cost) REFERENCES Creates_Ads0 
);


CREATE TABLE Badges(
      bname char(50) PRIMARY KEY,
      description char(80) NOT NULL
);

CREATE TABLE Receives_Ads(
	sid char(50),
	title char(50),
      username char(50),
      PRIMARY KEY(sid,title,username),
      FOREIGN KEY(sid, title) REFERENCES Creates_Ads1
            ON DELETE CASCADE,
      FOREIGN KEY(username) REFERENCES Users 
            ON DELETE CASCADE
);

CREATE TABLE Gets_Badges(
	username char(50),
	bname char(50),
	PRIMARY KEY(username,bname),
	FOREIGN KEY(username) REFERENCES Users 
            ON DELETE CASCADE,
	FOREIGN KEY(bname) REFERENCES Badges 
            ON DELETE CASCADE
);


CREATE TABLE Offers_Badges(
	sid char(50),
	title char(50),
      bname char(50),
      PRIMARY KEY(sid,title,bname),
      FOREIGN KEY(sid, title) REFERENCES Creates_Ads1 
            ON DELETE CASCADE,
      FOREIGN KEY(bname) REFERENCES Badges 
            ON DELETE CASCADE
);

CREATE TABLE Posts(
	pid char(10) PRIMARY KEY,
	ptext char(200) NOT NULL,
	timeCreated timestamp NOT NULL
);

CREATE TABLE News(
	pid char(10) PRIMARY KEY,
	reporter char(50) NOT NULL,
	FOREIGN KEY(pid) REFERENCES Posts(pid)
		ON DELETE CASCADE
);

CREATE TABLE Receives_News(
	pid char(10),
	username char(50),
	PRIMARY KEY (pid, username),
	FOREIGN KEY (pid) REFERENCES News(pid) 
            ON DELETE CASCADE,
	FOREIGN KEY (username) REFERENCES Users(username) 
            ON DELETE CASCADE
);

CREATE TABLE Relates(
	pid char(10),
	tname char(50),
	PRIMARY KEY (pid, tname),
	FOREIGN KEY (pid) REFERENCES Posts(pid) 
            ON DELETE CASCADE,
	FOREIGN KEY (tname) REFERENCES Topics(tname) 
            ON DELETE CASCADE
);

CREATE TABLE Groups(
	gname char(30) PRIMARY KEY,
	timeCreated date NOT NULL
);

CREATE TABLE Content(
	pid char(10) PRIMARY KEY,
	username char(50) NOT NULL,
	gname char(30),
	FOREIGN KEY(pid) REFERENCES Posts(pid)
		ON DELETE CASCADE,
	FOREIGN KEY(username) REFERENCES Users(username)
		ON DELETE CASCADE,
	FOREIGN KEY(gname) REFERENCES Groups(gname)
		ON DELETE CASCADE
);

CREATE TABLE Joins_Groups(
	username char(50),
	gname char(30),
	PRIMARY KEY (gname, username),
	FOREIGN KEY (gname) REFERENCES Groups(gname) 
            ON DELETE CASCADE,
	FOREIGN KEY (username) REFERENCES Users(username) 
            ON DELETE CASCADE
);

INSERT ALL
    INTO Users (username, password) VALUES ('user1', 'password1')
    INTO Users (username, password) VALUES ('user2', 'password2')
    INTO Users (username, password) VALUES ('user3', 'password3')
    INTO Users (username, password) VALUES ('user4', 'password4')
    INTO Users (username, password) VALUES ('user5', 'password5')
SELECT 1 FROM DUAL;

INSERT ALL
INTO Chats(cname) VALUES ('chatFor1&2')
INTO Chats(cname) VALUES ('chatFor1&3')
INTO Chats(cname) VALUES ('chatFor3&4')
INTO Chats(cname) VALUES ('chatFor2&5')
INTO Chats(cname) VALUES ('chatFor1&5')
SELECT 1 FROM DUAL;

INSERT ALL
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor1&2', 'user1', 'user2')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor1&2', 'user2', 'user1')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor1&3', 'user1', 'user3')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor1&3', 'user3', 'user1')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor1&5', 'user1', 'user5')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor1&5', 'user5', 'user1')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor2&5', 'user2', 'user5')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor2&5', 'user5', 'user2')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor3&4', 'user3', 'user4')
INTO Messages0(cname, senderName, receiverName) VALUES ('chatFor3&4', 'user4', 'user3')

SELECT 1 FROM DUAL;

INSERT ALL 
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&2', TIMESTAMP '2022-07-18 10:34:09.45', 'hi', 'user1')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&2', TIMESTAMP '2022-07-18 10:35:09.45', 'hello', 'user2')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&2', TIMESTAMP '2022-07-18 10:36:09.45', 'I am user1', 'user1')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&2', TIMESTAMP '2022-07-18 10:37:09.45', 'Call me user2!', 'user2')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&2', TIMESTAMP '2022-07-18 10:38:09.45', 'Nice to meet you', 'user2')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&3', TIMESTAMP '2022-07-18 10:36:09.45', 'hi', 'user1')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&3', TIMESTAMP '2022-07-18 10:37:09.45', 'I am user1', 'user1')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&5', TIMESTAMP '2022-07-18 10:36:09.45', 'hi', 'user1')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&5', TIMESTAMP '2022-07-18 10:37:09.45', 'I am user1', 'user1')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor1&5', TIMESTAMP '2022-07-18 10:38:09.45', 'hi user1', 'user5')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor2&5', TIMESTAMP '2022-07-18 10:35:09.45', 'hello', 'user2')
INTO Messages1(cname, time, mtext, senderName) VALUES ('chatFor2&5', TIMESTAMP '2022-07-18 10:36:09.45', 'how are you', 'user2')
SELECT 1 FROM DUAL;

INSERT ALL 
INTO Has_Chats(username, cname) VALUES ('user1', 'chatFor1&2')
INTO Has_Chats(username, cname) VALUES ('user1', 'chatFor1&3')
INTO Has_Chats(username, cname) VALUES ('user1', 'chatFor1&5')
INTO Has_Chats(username, cname) VALUES ('user2', 'chatFor1&2')
INTO Has_Chats(username, cname) VALUES ('user2', 'chatFor2&5')
INTO Has_Chats(username, cname) VALUES ('user3', 'chatFor1&3')
INTO Has_Chats(username, cname) VALUES ('user3', 'chatFor3&4')
INTO Has_Chats(username, cname) VALUES ('user4', 'chatFor3&4')
INTO Has_Chats(username, cname) VALUES ('user5', 'chatFor1&5')
INTO Has_Chats(username, cname) VALUES ('user5', 'chatFor2&5')
SELECT 1 FROM DUAL;

INSERT ALL 
INTO Topics(tname, numberOfPost) VALUES ('sunrise', 0)
INTO Topics(tname, numberOfPost) VALUES ('Sports', 0)
INTO Topics(tname, numberOfPost) VALUES ('Pets', 1)
INTO Topics(tname, numberOfPost) VALUES ('Sea', 0)
INTO Topics(tname, numberOfPost) VALUES ('Spring', 0)
INTO Topics(tname, numberOfPost) VALUES ('My Favorite Song', 0)
INTO Topics(tname, numberOfPost) VALUES ('My Day', 3)
INTO Topics(tname, numberOfPost) VALUES ('Coffee', 1)
INTO Topics(tname, numberOfPost) VALUES ('Diet', 0)
SELECT 1 FROM DUAL;

INSERT ALL
INTO Creates_Topics(tname, username) VALUES ('Spring','user1')
INTO Creates_Topics(tname, username) VALUES ('My Favorite Song', 'user1')
INTO Creates_Topics(tname, username) VALUES ('My Day', 'user2')
INTO Creates_Topics(tname, username) VALUES ('Coffee', 'user2')
INTO Creates_Topics(tname, username) VALUES ('Diet', 'user2')
SELECT 1 FROM DUAL;

INSERT ALL
    INTO Sponsors (sid, sname, password) VALUES ('s1', 'COFFEE', 'password1')
    INTO Sponsors (sid, sname, password) VALUES ('s2', 'TEA', 'password2')
    INTO Sponsors (sid, sname, password) VALUES ('s3', 'BOBA', 'password3')
    INTO Sponsors (sid, sname, password) VALUES ('s4', 'GEESE', 'password4')
    INTO Sponsors (sid, sname, password) VALUES ('s5', 'MATCHA', 'password5')
SELECT 1 FROM DUAL;

INSERT ALL
    INTO Creates_Ads0 (cost, displayTimeInDays) VALUES (100, 5)
    INTO Creates_Ads0 (cost, displayTimeInDays) VALUES (500, 30)
    INTO Creates_Ads0 (cost, displayTimeInDays) VALUES (1000, 65)
    INTO Creates_Ads0 (cost, displayTimeInDays) VALUES (2000,140)
    INTO Creates_Ads0 (cost, displayTimeInDays) VALUES (4000, 300)
SELECT 1 FROM DUAL;

INSERT ALL
    INTO Creates_Ads1 (sid, title, cost, description, timeCreated) VALUES ('s1', 'COFFEE1', 100, 'Freshly Brewed Coffee', TIMESTAMP '2022-07-18 10:34:09.45')
    INTO Creates_Ads1 (sid, title, cost, description, timeCreated) VALUES ('s3', 'BOBA1', 500, 'Amazing Black Sugar Boba', TIMESTAMP '2022-07-18 10:35:09.45')
    INTO Creates_Ads1 (sid, title, cost, description, timeCreated) VALUES ('s3', 'BOBA2', 500, 'Blue Lemonade', TIMESTAMP '2022-07-18 10:36:09.45')
    INTO Creates_Ads1 (sid, title, cost, description, timeCreated) VALUES ('s4', 'GEESE1', 500, 'The Best Animal, and the fiercest fighter', TIMESTAMP '2022-07-18 10:37:09.45')
    INTO Creates_Ads1 (sid, title, cost, description, timeCreated) VALUES ('s5', 'MATCHA1', 1000, 'The Supremacy', TIMESTAMP '2022-07-18 10:39:09.45')
    INTO Creates_Ads1 (sid, title, cost, description, timeCreated) VALUES ('s5', 'MATCHA2', 4000, 'The Oriental', TIMESTAMP '2022-07-18 10:44:09.45')
SELECT 1 FROM DUAL;

INSERT ALL
    INTO Badges (bname, description) VALUES ('b1', 'For Coffee Lover')
    INTO Badges (bname, description) VALUES ('b2', 'For Black Sugar Lover')
    INTO Badges (bname, description) VALUES ('b3', 'For Lemonade Lover')
    INTO Badges (bname, description) VALUES ('b4', 'For Matcha Lover')
    INTO Badges (bname, description) VALUES ('b5', 'Great User')
    INTO Badges (bname, description) VALUES ('b6', 'Honourable Geese')
SELECT 1 FROM DUAL;

INSERT ALL
    INTO Receives_Ads (sid, title, username) VALUES ('s1', 'COFFEE1', 'user1')
    INTO Receives_Ads (sid, title, username) VALUES ('s1', 'COFFEE1', 'user2')
    INTO Receives_Ads (sid, title, username) VALUES ('s1', 'COFFEE1', 'user3')
    INTO Receives_Ads (sid, title, username) VALUES ('s1', 'COFFEE1', 'user4')
    INTO Receives_Ads (sid, title, username) VALUES ('s1', 'COFFEE1', 'user5')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA1', 'user1')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA1', 'user2')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA1', 'user3')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA1', 'user4')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA1', 'user5')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA2', 'user1')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA2', 'user2')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA2', 'user3')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA2', 'user4')
    INTO Receives_Ads (sid, title, username) VALUES ('s3', 'BOBA2', 'user5')
    INTO Receives_Ads (sid, title, username) VALUES ('s4', 'GEESE1', 'user1')
    INTO Receives_Ads (sid, title, username) VALUES ('s4', 'GEESE1', 'user2')
    INTO Receives_Ads (sid, title, username) VALUES ('s4', 'GEESE1', 'user3')
    INTO Receives_Ads (sid, title, username) VALUES ('s4', 'GEESE1', 'user4')
    INTO Receives_Ads (sid, title, username) VALUES ('s4', 'GEESE1', 'user5')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA1', 'user1')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA1', 'user2')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA1', 'user3')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA1', 'user4')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA1', 'user5')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA2', 'user1')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA2', 'user2')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA2', 'user3')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA2', 'user4')
    INTO Receives_Ads (sid, title, username) VALUES ('s5', 'MATCHA2', 'user5')
SELECT 1 FROM DUAL;

INSERT ALL
    INTO Gets_Badges(username, bname) VALUES ('user1', 'b5')
    INTO Gets_Badges(username, bname) VALUES ('user1', 'b6')
    INTO Gets_Badges(username, bname) VALUES ('user2', 'b1')
    INTO Gets_Badges(username, bname) VALUES ('user2', 'b2')
    INTO Gets_Badges(username, bname) VALUES ('user2', 'b3')
    INTO Gets_Badges(username, bname) VALUES ('user2', 'b4')
    INTO Gets_Badges(username, bname) VALUES ('user2', 'b5')
    INTO Gets_Badges(username, bname) VALUES ('user2', 'b6')
    INTO Gets_Badges(username, bname) VALUES ('user3', 'b1')
    INTO Gets_Badges(username, bname) VALUES ('user3', 'b2')
    INTO Gets_Badges(username, bname) VALUES ('user3', 'b3')
    INTO Gets_Badges(username, bname) VALUES ('user3', 'b4')
    INTO Gets_Badges(username, bname) VALUES ('user3', 'b6')
    INTO Gets_Badges(username, bname) VALUES ('user4', 'b1')
    INTO Gets_Badges(username, bname) VALUES ('user4', 'b2')
    INTO Gets_Badges(username, bname) VALUES ('user4', 'b3')
    INTO Gets_Badges(username, bname) VALUES ('user4', 'b4')
    INTO Gets_Badges(username, bname) VALUES ('user4', 'b5')
    INTO Gets_Badges(username, bname) VALUES ('user4', 'b6')
    INTO Gets_Badges(username, bname) VALUES ('user5', 'b1')
    INTO Gets_Badges(username, bname) VALUES ('user5', 'b2')
    INTO Gets_Badges(username, bname) VALUES ('user5', 'b3')
    INTO Gets_Badges(username, bname) VALUES ('user5', 'b4')
    INTO Gets_Badges(username, bname) VALUES ('user5', 'b6')
SELECT 1 FROM DUAL;

INSERT ALL
    INTO Offers_Badges (sid, title, bname) VALUES ('s1', 'COFFEE1', 'b1')
    INTO Offers_Badges (sid, title, bname) VALUES ('s3', 'BOBA1', 'b2')
    INTO Offers_Badges (sid, title, bname) VALUES ('s3', 'BOBA2', 'b3')
    INTO Offers_Badges (sid, title, bname) VALUES ('s5', 'MATCHA1', 'b4')
    INTO Offers_Badges (sid, title, bname) VALUES ('s4', 'GEESE1', 'b6')
SELECT 1 FROM DUAL;

INSERT ALL
       INTO Posts(pid, ptext, timeCreated) VALUES('C123456789', 'Breka Café has the best hot chocolate!', TIMESTAMP '2022-07-18 10:34:09.45') 
       INTO Posts(pid, ptext, timeCreated) VALUES ('N888456781', 'Breaking News: Billionaire Sued for Backing Out of Purchase', TIMESTAMP '2022-07-18 10:35:09.45') 
       INTO Posts(pid, ptext, timeCreated) VALUES ('C123455553', 'Canadian Geese can sense fear', TIMESTAMP '2022-07-18 10:36:09.45') 
       INTO Posts(pid, ptext, timeCreated) VALUES ('N833746781', 'Boulder Found on Sea-to-Sky Highway', TIMESTAMP '2022-07-18 10:34:09.45') 
       INTO Posts(pid, ptext, timeCreated) VALUES ('C196478236', 'Why can cows stop burglars? They calcium!', TIMESTAMP '2022-07-18 10:37:09.45')  
       INTO Posts(pid, ptext, timeCreated) VALUES ('C163489553', 'Ice cream sandwich melted in my hands. Why do bad things happen to good people?', TIMESTAMP '2022-07-18 10:38:09.45') 
       INTO Posts(pid, ptext, timeCreated) VALUES ('C314504864', 'The sun and I have a love-hate relationship', TIMESTAMP '2022-07-18 10:39:09.45') 
       INTO Posts(pid, ptext, timeCreated) VALUES ('N257416580', 'New Species of Raccoon Discovered!', TIMESTAMP '2022-07-18 10:40:09.45') 
       INTO Posts(pid, ptext, timeCreated) VALUES ('N833698021', 'Expert Weighs in: Five Ways to Declutter Your Haunted Doll Room', TIMESTAMP '2022-07-18 10:41:09.45') 
       INTO Posts(pid, ptext, timeCreated) VALUES ('N394256710', 'Housing Market - The One Industry that Millennials Have Not Killed. Yet.', TIMESTAMP '2022-07-18 10:42:09.45') 
SELECT 1 FROM DUAL;

INSERT ALL 
       INTO Groups(gname, timeCreated) VALUES('group1', DATE '2022-04-01') 
       INTO Groups(gname, timeCreated) VALUES ('group2', DATE '2022-05-11') 
       INTO Groups(gname, timeCreated) VALUES ('group3', DATE '2022-06-30') 
       INTO Groups(gname, timeCreated) VALUES ('group4', DATE '2022-05-24') 
       INTO Groups(gname, timeCreated) VALUES ('group5', DATE '2022-07-11') SELECT 1 FROM DUAL;

INSERT ALL
       INTO Content(pid, username, gname) VALUES('C123455553', 'user1', 'group5')
       INTO Content(pid, username, gname) VALUES ('C196478236', 'user3', 'group3')
       INTO Content(pid, username, gname) VALUES ('C123456789', 'user2', NULL)
       INTO Content(pid, username, gname) VALUES ('C163489553', 'user4', NULL)
       INTO Content(pid, username, gname) VALUES  ('C314504864', 'user5', NULL)
SELECT 1 FROM DUAL;

INSERT ALL 
       INTO News(pid, reporter) VALUES ('N888456781', 'reporter1')
       INTO News(pid, reporter) VALUES ('N833746781', 'reporter2')
       INTO News(pid, reporter) VALUES ('N833698021', 'reporter3')
       INTO News(pid, reporter) VALUES ('N394256710', 'reporter4')
       INTO News(pid, reporter) VALUES ('N257416580', 'reporter5')
SELECT 1 FROM DUAL;

INSERT ALL
       INTO Relates(pid, tname) VALUES ('C123455553', 'Coffee')
       INTO Relates(pid, tname) VALUES ('C123456789', 'My Day')
       INTO Relates(pid, tname) VALUES ('C196478236', 'Pets')
       INTO Relates(pid, tname) VALUES ('N888456781', 'My Day')
       INTO Relates(pid, tname) VALUES ('N833698021', 'My Day')
SELECT 1 FROM DUAL;

INSERT ALL 
       INTO Joins_Groups(username, gname) VALUES ('user1', 'group1')
       INTO Joins_Groups(username, gname) VALUES ('user2', 'group2')
       INTO Joins_Groups(username, gname) VALUES ('user3', 'group3')
       INTO Joins_Groups(username, gname) VALUES ('user2', 'group4')
       INTO Joins_Groups(username, gname) VALUES  ('user1', 'group5')
SELECT 1 FROM DUAL;

INSERT ALL 
       INTO Receives_News(pid, username) VALUES ('N888456781', 'user1')
       INTO Receives_News(pid, username) VALUES ('N888456781', 'user2')
       INTO Receives_News(pid, username) VALUES ('N888456781', 'user3')
       INTO Receives_News(pid, username) VALUES ('N888456781', 'user4')
       INTO Receives_News(pid, username) VALUES ('N888456781', 'user5')
       INTO Receives_News(pid, username) VALUES ('N833746781', 'user1')
       INTO Receives_News(pid, username) VALUES ('N833746781', 'user2')
       INTO Receives_News(pid, username) VALUES ('N833746781', 'user3')
       INTO Receives_News(pid, username) VALUES ('N833746781', 'user4')
       INTO Receives_News(pid, username) VALUES ('N833746781', 'user5')
       INTO Receives_News(pid, username) VALUES ('N833698021', 'user1')
       INTO Receives_News(pid, username) VALUES ('N833698021', 'user2')
       INTO Receives_News(pid, username) VALUES ('N833698021', 'user3')
       INTO Receives_News(pid, username) VALUES ('N833698021', 'user4')
       INTO Receives_News(pid, username) VALUES ('N833698021', 'user5')
       INTO Receives_News(pid, username) VALUES ('N394256710', 'user1')
       INTO Receives_News(pid, username) VALUES ('N394256710', 'user2')
       INTO Receives_News(pid, username) VALUES ('N394256710', 'user3')
       INTO Receives_News(pid, username) VALUES ('N394256710', 'user4')
       INTO Receives_News(pid, username) VALUES ('N394256710', 'user5')
       INTO Receives_News(pid, username) VALUES ('N257416580', 'user1')
       INTO Receives_News(pid, username) VALUES ('N257416580', 'user2')
       INTO Receives_News(pid, username) VALUES ('N257416580', 'user3')
       INTO Receives_News(pid, username) VALUES ('N257416580', 'user4')
       INTO Receives_News(pid, username) VALUES ('N257416580', 'user5')
SELECT 1 FROM DUAL;