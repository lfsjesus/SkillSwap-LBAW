DROP SCHEMA IF EXISTS skillswap CASCADE;
CREATE SCHEMA IF NOT EXISTS skillswap;
SET search_path TO skillswap;


DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS posts CASCADE;
DROP TABLE IF EXISTS groups CASCADE;
DROP TABLE IF EXISTS comments CASCADE;
DROP TABLE IF EXISTS files CASCADE;
DROP TABLE IF EXISTS likes CASCADE;
DROP TABLE IF EXISTS is_friend CASCADE;
DROP TABLE IF EXISTS user_blocks CASCADE;
DROP TABLE IF EXISTS is_member CASCADE;
DROP TABLE IF EXISTS owns CASCADE;
DROP TABLE IF EXISTS group_blocks CASCADE;
DROP TABLE IF EXISTS administrators CASCADE;
DROP TABLE IF EXISTS user_ban CASCADE;
DROP TABLE IF EXISTS user_create CASCADE;
DROP TABLE IF EXISTS user_edit CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS comment_notifications CASCADE;
DROP TABLE IF EXISTS post_notifications CASCADE;
DROP TABLE IF EXISTS group_notifications CASCADE;
DROP TABLE IF EXISTS user_notifications CASCADE;

DROP TYPE IF EXISTS field_types CASCADE;
DROP TYPE IF EXISTS comment_notification_types CASCADE;
DROP TYPE IF EXISTS post_notification_types CASCADE;
DROP TYPE IF EXISTS group_notification_types CASCADE;
DROP TYPE IF EXISTS user_notification_types CASCADE;

/*
DOMAINS/TYPES
*/

CREATE TYPE field_types AS ENUM('name', 'username', 'e-mail', 'password', 'description');
CREATE TYPE comment_notification_types AS ENUM('like_comment', 'new_comment', 'reply_comment', 'tag_comment');
CREATE TYPE post_notification_types AS ENUM('like_post', 'tag_post');
CREATE TYPE group_notification_types AS ENUM('join_request', 'join_accept', 'ban', 'kick', 'invite');
CREATE TYPE user_notification_types AS ENUM('friend_request', 'accepted_request');

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password TEXT NOT NULL,
    phone_number VARCHAR(50),
    profile_picture BYTEA,
    description VARCHAR(300),
    birth_date DATE NOT NULL,
    CONSTRAINT check_age CHECK (birth_date <= CURRENT_DATE - INTERVAL '18' YEAR),
    public_profile BOOLEAN DEFAULT true
);

CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    banner BYTEA,
    description VARCHAR(300),
    public_group BOOLEAN DEFAULT false,
    date DATE NOT NULL,
    CHECK (date <= CURRENT_DATE)
);

CREATE TABLE posts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ,
    group_id INTEGER REFERENCES groups(id)  ON DELETE CASCADE,
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP),
    description TEXT,
    public_post BOOLEAN DEFAULT true
);

CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id)  ON DELETE SET NULL, 
    post_id INTEGER NOT NULL REFERENCES posts(id)  ON DELETE CASCADE,
    comment_id INTEGER REFERENCES comments(id)  ON DELETE CASCADE,
    content TEXT,
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP)

);

CREATE TABLE files (
    id SERIAL PRIMARY KEY,
    post_id INTEGER REFERENCES posts(id)  ON DELETE CASCADE,
    comment_id INTEGER REFERENCES comments(id)  ON DELETE CASCADE,
    CHECK (post_id IS NULL AND comment_id IS NOT NULL OR post_id IS NOT NULL AND comment_id IS NULL),
    title VARCHAR(50) NOT NULL,
    files BYTEA NOT NULL,
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP)
);

CREATE TABLE likes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    post_id INTEGER REFERENCES posts(id)  ON DELETE CASCADE,
    comment_id INTEGER REFERENCES comments(id)  ON DELETE CASCADE,
    CONSTRAINT unique_like UNIQUE (user_id, post_id, comment_id),
    CHECK (post_id IS NULL AND comment_id IS NOT NULL OR post_id IS NOT NULL AND comment_id IS NULL),
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP)
);

CREATE TABLE is_friend (
    user_id INTEGER NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    friend_id INTEGER NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    PRIMARY KEY (user_id, friend_id),
    CHECK (user_id <> friend_id),
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP)
);

CREATE TABLE user_blocks (
    blocked_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    blocked_user INTEGER REFERENCES users(id) ON DELETE CASCADE,
    PRIMARY KEY (blocked_by, blocked_user),
    CONSTRAINT same_user CHECK (blocked_by <> blocked_user)
);

CREATE TABLE is_member (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, group_id),
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP)
);

CREATE TABLE owns (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP)
);

CREATE TABLE group_blocks (
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    blocked_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    blocked_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP),
    PRIMARY KEY (group_id, blocked_user),
    CONSTRAINT same_user CHECK (blocked_by <> blocked_user) /* check on uml and relational model */
);

CREATE TABLE administrators (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password TEXT NOT NULL
);

CREATE TABLE user_ban (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    administrator_id INTEGER NOT NULL REFERENCES administrators(id),
    days INTEGER NOT NULL,
    CHECK (days > 0),
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP)
);

CREATE TABLE user_create (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    administrator_id INTEGER NOT NULL REFERENCES administrators(id),
    date TIMESTAMP NOT NULL,
    name VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    password TEXT NOT NULL,
    birth_date DATE NOT NULL,
    CHECK (birth_date <= CURRENT_DATE - INTERVAL '18' YEAR),
    public_profile BOOLEAN DEFAULT true
);

CREATE TABLE user_edit (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    administrator_id INTEGER NOT NULL REFERENCES administrators(id),
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP),
    field_type field_types NOT NULL
);

CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    sender_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiver_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    date TIMESTAMP NOT NULL,
    CHECK (date <= CURRENT_TIMESTAMP),
    viewed BOOLEAN DEFAULT false
);

CREATE TABLE comment_notifications (
    notification_id INTEGER REFERENCES notifications(id) ON DELETE CASCADE PRIMARY KEY,
    comment_id INTEGER NOT NULL REFERENCES comments(id) ON DELETE CASCADE,
    notification_type comment_notification_types NOT NULL
);

CREATE TABLE post_notifications (
    notification_id INTEGER REFERENCES notifications(id) ON DELETE CASCADE PRIMARY KEY,
    post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    notification_type post_notification_types NOT NULL
);

CREATE TABLE group_notifications (
    notification_id INTEGER REFERENCES notifications(id) ON DELETE CASCADE PRIMARY KEY,
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    notification_type group_notification_types NOT NULL
);

CREATE TABLE user_notifications (
    notification_id INTEGER REFERENCES notifications(id) ON DELETE CASCADE PRIMARY KEY,
    notification_type user_notification_types NOT NULL
);


/**
 * TRIGGERS
 */


CREATE OR REPLACE FUNCTION check_comment_date() RETURNS TRIGGER AS $$
    BEGIN
        IF (NEW.comment_id IS NULL) THEN
            IF (NEW.date < (SELECT date FROM posts WHERE id = NEW.post_id)) THEN
                RAISE EXCEPTION 'Comment date must be after post date';
            END IF;
        ELSE
            IF (NEW.date < (SELECT date FROM comments WHERE id = NEW.comment_id)) THEN
                RAISE EXCEPTION 'Comment date must be after comment date';
            END IF;
        END IF;
        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER check_comment_date
    BEFORE INSERT OR UPDATE ON comments
    FOR EACH ROW
    EXECUTE PROCEDURE check_comment_date();



CREATE OR REPLACE FUNCTION check_like_date() RETURNS TRIGGER AS $$
    BEGIN
        IF (NEW.post_id IS NULL) THEN
            IF (NEW.date < (SELECT date FROM comments WHERE id = NEW.comment_id)) THEN
                RAISE EXCEPTION 'Like date must be after comment date';
            END IF;
        ELSE
            IF (NEW.date < (SELECT date FROM posts WHERE id = NEW.post_id)) THEN
                RAISE EXCEPTION 'Like date must be after post date';
            END IF;
        END IF;
        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_like_date
    BEFORE INSERT OR UPDATE ON likes
    FOR EACH ROW
    EXECUTE PROCEDURE check_like_date();




CREATE OR REPLACE FUNCTION check_like_validity() RETURNS TRIGGER AS $$
BEGIN
    IF (SELECT group_id FROM posts WHERE id = NEW.post_id) IS NOT NULL THEN
        IF (NEW.user_id NOT IN (SELECT user_id FROM is_member WHERE group_id = (SELECT group_id FROM posts WHERE id = NEW.post_id))) THEN
            RAISE EXCEPTION 'User must be a member of the group to like a post';
        END IF;
    ELSE
        IF (
            NEW.post_id NOT IN (SELECT id FROM posts WHERE public_post = true)
            AND NEW.user_id NOT IN (SELECT friend_id FROM is_friend WHERE user_id = (SELECT user_id FROM posts WHERE id = NEW.post_id))
        ) THEN
            RAISE EXCEPTION 'User can only like public posts or posts of friends';
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER check_like_validity
    BEFORE INSERT OR UPDATE ON likes
    FOR EACH ROW
    EXECUTE PROCEDURE check_like_validity();



CREATE OR REPLACE FUNCTION check_comment_validity() RETURNS TRIGGER AS $$
BEGIN 
    /* group_id of the post that comment is being made on */
    IF (SELECT group_id FROM posts WHERE id = NEW.post_id) IS NOT NULL THEN
        IF (NEW.user_id NOT IN (SELECT user_id FROM is_member WHERE group_id = (SELECT group_id FROM posts WHERE id = NEW.post_id))) THEN
            RAISE EXCEPTION 'User must be a member of the group to comment on a post';
        END IF;
    ELSE
        IF (NEW.post_id NOT IN (SELECT id FROM posts WHERE public_post = true) 
                                AND NEW.user_id NOT IN 
                                (SELECT friend_id FROM is_friend WHERE user_id = (SELECT user_id FROM posts WHERE id = NEW.post_id))) THEN
            RAISE EXCEPTION 'User can only comment on public posts or posts of friends';
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER check_comment_validity
    BEFORE INSERT OR UPDATE ON comments
    FOR EACH ROW
    EXECUTE PROCEDURE check_comment_validity();




CREATE OR REPLACE FUNCTION add_friend() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.user_id <> NEW.friend_id) THEN
        IF NOT EXISTS (
            SELECT 1
            FROM is_friend
            WHERE user_id = NEW.friend_id
            AND friend_id = NEW.user_id
        ) THEN
            INSERT INTO is_friend (user_id, friend_id, date) VALUES (NEW.friend_id, NEW.user_id, NEW.date);
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER add_friend
    AFTER INSERT ON is_friend
    FOR EACH ROW
    WHEN (NEW.user_id <> NEW.friend_id)
    EXECUTE FUNCTION add_friend();




CREATE OR REPLACE FUNCTION check_file_format()
RETURNS TRIGGER AS $$
BEGIN
    IF RIGHT(NEW.title, 4) NOT IN ('.jpg', '.png', '.wav', '.mp4', '.mov') AND RIGHT(NEW.title, 5) NOT IN ('.jpeg', '.pdf') THEN
        RAISE EXCEPTION 'File format not allowed. Only .jpg, .jpeg, .png, .mp4, .mov, .wav and .pdf are allowed.';
        RETURN NULL;
    ELSE
        RETURN NEW;
    END IF;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER trigger_check_file_format
BEFORE INSERT ON files
FOR EACH ROW
EXECUTE FUNCTION check_file_format();



CREATE OR REPLACE FUNCTION ensure_owner_is_member() RETURNS TRIGGER AS $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM is_member WHERE user_id = NEW.user_id AND group_id = NEW.group_id) THEN
        INSERT INTO is_member(user_id, group_id, date) VALUES (NEW.user_id, NEW.group_id, CURRENT_TIMESTAMP);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_ensure_owner_is_member
AFTER INSERT ON owns
FOR EACH ROW
EXECUTE FUNCTION ensure_owner_is_member();





CREATE OR REPLACE FUNCTION check_user_group_membership_to_post()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.group_id IS NOT NULL AND 
       NOT EXISTS (SELECT 1 
                   FROM is_member 
                   WHERE user_id = NEW.user_id AND group_id = NEW.group_id) THEN
        RAISE EXCEPTION 'User does not belong to the group!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER trigger_check_user_group_membership_to_post
BEFORE INSERT ON posts
FOR EACH ROW
EXECUTE FUNCTION check_user_group_membership_to_post();


-- Blocked users stop being friends (TO BE TESTED)
CREATE OR REPLACE FUNCTION delete_friendship()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM is_friend WHERE user_id = NEW.blocked_by AND friend_id = NEW.blocked_user) THEN
        DELETE FROM is_friend WHERE user_id = NEW.blocked_by AND friend_id = NEW.blocked_user;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_delete_friendship
AFTER INSERT ON user_blocks
FOR EACH ROW
EXECUTE FUNCTION delete_friendship();


-- User cant send a friend request to some user which he is already friends with

CREATE OR REPLACE FUNCTION prevent_duplicate_friend_requests()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
            SELECT 1
            FROM is_friend
            WHERE user_id = NEW.user_id
            AND friend_id = NEW.friend_id
        ) THEN
        RAISE EXCEPTION 'Users are already friends!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_prevent_duplicate_friend_requests
BEFORE INSERT ON is_friend
FOR EACH ROW
EXECUTE FUNCTION prevent_duplicate_friend_requests();


-- User cant send a join group request to a group which he is already member or he has notifcation where he was banned

CREATE OR REPLACE FUNCTION prevent_duplicate_join_requests()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
            SELECT 1
            FROM is_member
            WHERE user_id = NEW.user_id
            AND group_id = NEW.group_id)

            THEN
            RAISE EXCEPTION 'User is already a member of the group!';
    END IF;
           
    IF EXISTS(
            SELECT 1
            FROM group_notifications
            JOIN notifications ON group_notifications.notification_id = notifications.id
            WHERE notification_type = 'ban'
            AND group_id = NEW.group_id
            AND receiver_id = NEW.user_id)

            THEN
            RAISE EXCEPTION 'User is already a member of the group!';

    END IF;

    RETURN NEW;
END;

$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_prevent_duplicate_join_requests
BEFORE INSERT ON is_member
FOR EACH ROW
EXECUTE FUNCTION prevent_duplicate_join_requests();




/**
* INDEXES
*/


CREATE INDEX idx_receiver_notification ON notifications USING btree (receiver_id);
CLUSTER notifications USING idx_receiver_notification;


CREATE INDEX idx_notifications_sender ON notifications USING btree (sender_id);
CLUSTER notifications USING idx_notifications_sender;


CREATE INDEX user_id_comment ON comments USING hash (user_id);




-- Adding a column to store computed ts_vectors
ALTER TABLE users ADD COLUMN tsvectors TSVECTOR;
-- Creating a function to automatically update ts_vectors
CREATE OR REPLACE FUNCTION user_search_update() RETURNS TRIGGER AS $$
BEGIN
-- Check if the operation is INSERT or if relevant fields are updated in case of an UPDATE
 IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND (NEW.name IS DISTINCT FROM OLD.name OR NEW.username IS DISTINCT FROM OLD.username)) THEN

 -- Update the tsvectors column by concatenating weighted tsvectors of name and username columns
 NEW.tsvectors := (
                    setweight(to_tsvector('english', COALESCE(NEW.name, '')), 'A') ||
                    setweight(to_tsvector('english', COALESCE(NEW.username, '')), 'B')
                    );
END IF;
-- Return the NEW record for the operation to proceed
RETURN NEW;
END;$$ 
LANGUAGE plpgsql;

-- Creating a trigger to call the function before INSERT or UPDATE operations on users table
CREATE TRIGGER user_search_update BEFORE INSERT OR UPDATE ON users
FOR EACH ROW EXECUTE PROCEDURE user_search_update();





-- Creating a GIN index to optimize text search on the tsvectors column
CREATE INDEX search_user ON users USING GIN (tsvectors);

ALTER TABLE groups ADD COLUMN tsvectors TSVECTOR;

-- Create a function to automatically update ts_vectors

CREATE OR REPLACE FUNCTION g_search_update() RETURNS TRIGGER AS $$

BEGIN

IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND (NEW.name <> OLD.name OR NEW.description <> OLD.description)) THEN

NEW.tsvectors := ( 
                  setweight(to_tsvector('english', NEW.name), 'A') || 
                  setweight(to_tsvector('english', COALESCE(NEW.description, '')), 'B')

);

END IF;

RETURN NEW;

END $$ LANGUAGE plpgsql;

-- Create a trigger before insert or update on groups

CREATE TRIGGER g_search_update

BEFORE INSERT OR UPDATE ON groups

FOR EACH ROW

EXECUTE FUNCTION g_search_update();

-- Create a GIN index for ts_vectors

CREATE INDEX search_g ON groups USING GIN (tsvectors);


/*Administrators */
insert into administrators (name, username, email, password) values ('Roderic Gullam', 'admin0848605704823569', 'rgullam0@topsy.com', 'iS7\_uj>');
insert into administrators (name, username, email, password) values ('Richie Pelman', 'admin889845340', 'rpelman1@slashdot.org', 'dM8+Iw\{GN3|');
insert into administrators (name, username, email, password) values ('Chico Jaine', 'admin558881307', 'cjaine2@indiegogo.com', 'sC9,2g+=4/w.<5y');
insert into administrators (name, username, email, password) values ('Celestine Carlan', 'admin0442456064', 'ccarlan3@mit.edu', 'iG6~R/fR,Xhi=Zn6');
insert into administrators (name, username, email, password) values ('Isador Mokes', 'admin75107213', 'imokes4@dion.ne.jp', 'nV0,4>7C#W');
insert into administrators (name, username, email, password) values ('Yolande Clifton', 'admin7138882562', 'yclifton5@wiley.com', 'aR8''ogw6G+''X0|_P');
insert into administrators (name, username, email, password) values ('Marianna Timbs', 'admin8126301564', 'mtimbs6@1688.com', 'oE0{rO1\yl''M');
insert into administrators (name, username, email, password) values ('Caitrin Launder', 'admin7832', 'claunder7@businesswire.com', 'qH6)uy''q?');
insert into administrators (name, username, email, password) values ('Madelon Jeskins', 'admin1971936', 'mjeskins8@linkedin.com', 'kI0(k<vYeKJglb');
insert into administrators (name, username, email, password) values ('Ado Allott', 'admin052219159', 'aallott9@ca.gov', 'cM8#/ENN}HEY');
insert into administrators (name, username, email, password) values ('Cherin Bruntje', 'admin8910140', 'cbruntjea@baidu.com', 'oL6''q(1<Bw>@%xO');
insert into administrators (name, username, email, password) values ('Brodie Wincott', 'admin866148', 'bwincottb@addtoany.com', 'mS6*u7q&L');
insert into administrators (name, username, email, password) values ('Yorgo Yashanov', 'admin157621275629', 'yyashanovc@nbcnews.com', 'uM4>4L?a!');
insert into administrators (name, username, email, password) values ('Ulric Arson', 'admin786428380743356181', 'uarsond@cpanel.net', 'tY5@`<3e(X+mG');
insert into administrators (name, username, email, password) values ('Christel Beckhouse', 'admin48', 'cbeckhousee@intel.com', 'wR8#&1~Z5X');
insert into administrators (name, username, email, password) values ('Yurik Bythell', 'admin8230279829246364', 'ybythellf@nature.com', 'uB9!.FT1''6HOc');
insert into administrators (name, username, email, password) values ('Seamus Jarrell', 'admin165', 'sjarrellg@friendfeed.com', 'mM0&Ktbe}"Q3XRV>');
insert into administrators (name, username, email, password) values ('Giraldo Anscombe', 'admin24', 'ganscombeh@mayoclinic.com', 'bP1?Dzu}');
insert into administrators (name, username, email, password) values ('Vanessa Garretson', 'admin98925389826581525922', 'vgarretsoni@wikimedia.org', 'rV8<@G#k?M''I');
insert into administrators (name, username, email, password) values ('Mildred Gostage', 'admin7485974549653340', 'mgostagej@tuttocitta.it', 'pZ3~jSNy?||d/');
insert into administrators (name, username, email, password) values ('Iago Fominov', 'admin126770', 'ifominovk@ibm.com', 'iK8''rntn?');
insert into administrators (name, username, email, password) values ('Gonzalo Mauser', 'admin386524012', 'gmauserm@creativecommons.org', 'zO9#{qsaDvkw');
insert into administrators (name, username, email, password) values ('Vinny Merrien', 'admin3519271', 'vmerrienn@rediff.com', 'mE8)cxJe');
insert into administrators (name, username, email, password) values ('Gianni Engeham', 'admin07809132254909566843', 'gengehamo@blogtalkradio.com', 'vF2#+ct{');
insert into administrators (name, username, email, password) values ('Caz Cunrado', 'admin569228396299369', 'ccunradop@live.com', 'uL0\gYf*)xFphrlD');
insert into administrators (name, username, email, password) values ('Donelle Krelle', 'admin559925645511269879', 'dkrelleq@topsy.com', 'gQ1|Mc2{O4iIzm8)');
insert into administrators (name, username, email, password) values ('Kaitlynn Gisborne', 'admin2054000088611227054', 'kgisborner@cnet.com', 'wR7}9BdOrgs9GEu');
insert into administrators (name, username, email, password) values ('Gabriel Kilfeather', 'admin44', 'gkilfeathers@mozilla.org', 'cF5,G_7#HIDs(DN');
insert into administrators (name, username, email, password) values ('Bentley Huelin', 'admin9221093477235', 'bhuelint@biblegateway.com', 'pM9,?L*rND');
insert into administrators (name, username, email, password) values ('Alvin Poupard', 'admin492467800242', 'apoupardu@house.gov', 'bQ7|gsG_C$YH');
insert into administrators (name, username, email, password) values ('Parrnell Insole', 'admin834936152029', 'pinsolev@nsw.gov.au', 'uJ5>PK/cER$tW}');
insert into administrators (name, username, email, password) values ('Whitman Szreter', 'admin37', 'wszreterw@webmd.com', 'rR1(o?%3Xamf!_');
insert into administrators (name, username, email, password) values ('Dyane Cordes', 'admin2291818398301361168', 'dcordesx@hc360.com', 'mU0&i~vz<R');
insert into administrators (name, username, email, password) values ('Enrika Baylay', 'admin34298', 'ebaylayy@soundcloud.com', 'oM4"Am!D~d{kI#');
insert into administrators (name, username, email, password) values ('Ronica Ortes', 'admin4534339785288320520', 'rortesz@naver.com', 'fY6.bMe#F8?');
insert into administrators (name, username, email, password) values ('Wyn Josefson', 'admin453930421208', 'wjosefson10@youtube.com', 'iY1|Hg7A%N8V}d');
insert into administrators (name, username, email, password) values ('Guinevere Phlipon', 'admin54674', 'gphlipon11@example.com', 'eT7+#Ip)z');
insert into administrators (name, username, email, password) values ('Joelie Eliasson', 'admin4545244', 'jeliasson12@sfgate.com', 'qC7''ORpeS|');
insert into administrators (name, username, email, password) values ('Cordula Blumson', 'admin5041', 'cblumson13@ft.com', 'nK1&~If,');


/*Users*/

insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Arnie Hawarden', 'ahawarden0', 'ahawarden0@myspace.com', 'aN3=rr.P)8', '6134246617', 'https://robohash.org/placeatsuscipitreprehenderit.png?size=50x50&set=set1', 'Business-focused full-range Graphical User Interface', '1986-08-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Donal Titmarsh', 'dtitmarsh1', 'dtitmarsh1@msu.edu', 'yS5&l2dB_1<HW', '8292288149', 'https://robohash.org/remmollitialaudantium.png?size=50x50&set=set1', 'Right-sized bifurcated architecture', '1985-05-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Arron Dumini', 'adumini2', 'adumini2@chicagotribune.com', 'yT9,4lh@%S', '4745655445', 'https://robohash.org/eumcommodirecusandae.png?size=50x50&set=set1', 'Digitized high-level methodology', '1973-05-27', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Darryl Howley', 'dhowley3', 'dhowley3@shutterfly.com', 'aR2`iW"m)', '7453942254', 'https://robohash.org/idquamtemporibus.png?size=50x50&set=set1', 'Networked local task-force', '1972-02-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Afton Wagerfield', 'awagerfield4', 'awagerfield4@businessweek.com', 'zY7)Is~{o', '6088155062', 'https://robohash.org/porroquismolestiae.png?size=50x50&set=set1', 'Extended neutral knowledge user', '1986-08-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Patti Boow', 'pboow5', 'pboow5@umn.edu', 'eQ9/NN%imGdeEy', '4549781650', 'https://robohash.org/voluptatemquisperspiciatis.png?size=50x50&set=set1', 'Operative attitude-oriented service-desk', '1977-01-05', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Reuven Schimank', 'rschimank6', 'rschimank6@surveymonkey.com', 'iZ5>OrwK/>v', '7266744847', 'https://robohash.org/uteteius.png?size=50x50&set=set1', 'Public-key object-oriented encryption', '2000-05-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Norean Addyman', 'naddyman7', 'naddyman7@guardian.co.uk', 'xX6=<TLqe', '8462049412', 'https://robohash.org/praesentiumipsaearum.png?size=50x50&set=set1', 'Secured encompassing circuit', '2001-08-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tracey Bazoche', 'tbazoche8', 'tbazoche8@e-recht24.de', 'pO8~`N\SMXk4>W(S', '6633586236', 'https://robohash.org/maioresexpeditaaut.png?size=50x50&set=set1', 'Intuitive empowering leverage', '2005-07-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lyndsay Mateja', 'lmateja9', 'lmateja9@naver.com', 'oE2&pf!iwB', '7999448482', 'https://robohash.org/aliquididodit.png?size=50x50&set=set1', 'Quality-focused directional monitoring', '1990-04-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Horacio Goldthorp', 'hgoldthorpa', 'hgoldthorpa@ebay.co.uk', 'sZ5!Pae)wMs_nF=', '6126303256', 'https://robohash.org/molestiaspraesentiumminus.png?size=50x50&set=set1', 'User-centric local focus group', '1992-09-15', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Wendeline Tollerfield', 'wtollerfieldb', 'wtollerfieldb@stumbleupon.com', 'jL0.vQ=NSP`', '9099617867', 'https://robohash.org/nesciuntvoluptasconsequatur.png?size=50x50&set=set1', 'Managed stable open architecture', '2001-12-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Benita Suddell', 'bsuddellc', 'bsuddellc@ftc.gov', 'vK1!L"f0wcJKxzB', '9793533122', 'https://robohash.org/quodetharum.png?size=50x50&set=set1', 'Down-sized systematic frame', '1992-05-03', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rube Lazarus', 'rlazarusd', 'rlazarusd@cam.ac.uk', 'iP3,{@f_k=$bJW', '6751118434', 'https://robohash.org/distinctiodoloremnisi.png?size=50x50&set=set1', 'Intuitive attitude-oriented challenge', '1996-04-17', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Terencio Ahlin', 'tahline', 'tahline@feedburner.com', 'mE4%TH''>''Kr#aj', '5848993892', 'https://robohash.org/molestiaequidemdeserunt.png?size=50x50&set=set1', 'Switchable homogeneous structure', '1995-09-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Desi Roseveare', 'drosevearef', 'drosevearef@mlb.com', 'hQ2,5em3{$u3+On', '5246932074', 'https://robohash.org/sitlaboriosamaccusamus.png?size=50x50&set=set1', 'Implemented dynamic analyzer', '2000-01-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Darlene Hobbema', 'dhobbemag', 'dhobbemag@ow.ly', 'bL6.,L_SAo', '7218450443', 'https://robohash.org/temporaquisquamaut.png?size=50x50&set=set1', 'Sharable multimedia system engine', '1999-09-03', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Antoine Lornsen', 'alornsenh', 'alornsenh@amazon.de', 'qN6@olBzdkw\6', '8991923149', 'https://robohash.org/cupiditateinventorepraesentium.png?size=50x50&set=set1', 'Switchable discrete structure', '1972-12-17', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Shayla Worsham', 'sworshami', 'sworshami@blogger.com', 'oC0|~Wp!QAH4JW0?', '1584478374', 'https://robohash.org/corporisdoloresqui.png?size=50x50&set=set1', 'Ameliorated cohesive paradigm', '1972-03-25', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Randa Quakley', 'rquakleyj', 'rquakleyj@1688.com', 'mP1?S`kcAXoq', '3597142206', 'https://robohash.org/quoautaut.png?size=50x50&set=set1', 'Organic systemic attitude', '1978-01-28', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Luisa Kitchenham', 'lkitchenhamk', 'lkitchenhamk@whitehouse.gov', 'fL9''JkVLr', '9741798486', 'https://robohash.org/etullamdolorem.png?size=50x50&set=set1', 'Up-sized solution-oriented local area network', '1985-04-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Fernando Lanon', 'flanonl', 'flanonl@mysql.com', 'dJ3_{*7|Ti&N2!wl', '1578252300', 'https://robohash.org/doloremquemolestiaset.png?size=50x50&set=set1', 'User-friendly clear-thinking attitude', '1992-06-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dmitri Kidgell', 'dkidgellm', 'dkidgellm@gizmodo.com', 'wR7!3"N1P\', '5209399167', 'https://robohash.org/nonmaioresanimi.png?size=50x50&set=set1', 'Grass-roots systematic adapter', '1995-08-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cathy Messiter', 'cmessitern', 'cmessitern@google.fr', 'xU9?lOdGI9', '4426291798', 'https://robohash.org/reiciendisaperiammolestiae.png?size=50x50&set=set1', 'Up-sized upward-trending neural-net', '1976-06-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dorotea Kaasman', 'dkaasmano', 'dkaasmano@paginegialle.it', 'zY2`Kq1@N&W', '1834006780', 'https://robohash.org/odiodoloremlaboriosam.png?size=50x50&set=set1', 'Assimilated bandwidth-monitored open system', '1978-04-02', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Justinian Malsher', 'jmalsherp', 'jmalsherp@chron.com', 'hG8,&,kqY13m', '3044579500', 'https://robohash.org/voluptassintodio.png?size=50x50&set=set1', 'Multi-lateral clear-thinking architecture', '1975-11-14', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Livvie Fluit', 'lfluitq', 'lfluitq@exblog.jp', 'nL0~0Mmn6nB9W''Yj', '4971893147', 'https://robohash.org/estbeataeut.png?size=50x50&set=set1', 'Right-sized 4th generation extranet', '1989-05-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Allie Furniss', 'afurnissr', 'afurnissr@networksolutions.com', 'sL3!h}SSo', '9618833969', 'https://robohash.org/ipsamsolutadistinctio.png?size=50x50&set=set1', 'Secured foreground success', '1980-06-16', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tiebout Malin', 'tmalins', 'tmalins@newyorker.com', 'sD4~EE''WPzdmH>8', '5417927398', 'https://robohash.org/eosconsecteturdolorem.png?size=50x50&set=set1', 'Fundamental foreground superstructure', '1975-10-24', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Benedicta Klezmski', 'bklezmskit', 'bklezmskit@constantcontact.com', 'iH0,6V32?J"nm', '8814730701', 'https://robohash.org/etatquequae.png?size=50x50&set=set1', 'Balanced empowering firmware', '1976-01-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Clerissa Simmank', 'csimmanku', 'csimmanku@apache.org', 'pS4&)@XZbsu', '8869476710', 'https://robohash.org/ducimusfaciliscorporis.png?size=50x50&set=set1', 'Reverse-engineered transitional secured line', '2005-05-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rhiamon Gribble', 'rgribblev', 'rgribblev@tiny.cc', 'aD1$3WGZ*30pH_', '1775660078', 'https://robohash.org/autemnemoducimus.png?size=50x50&set=set1', 'Operative interactive definition', '1989-08-22', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cyrillus MacWhirter', 'cmacwhirterw', 'cmacwhirterw@photobucket.com', 'kX7,''J"!K<ibv', '6866158453', 'https://robohash.org/maximeautet.png?size=50x50&set=set1', 'Ameliorated fault-tolerant contingency', '1981-05-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Skyler Nijssen', 'snijssenx', 'snijssenx@cbslocal.com', 'oI1$b.vhS3_ro', '8795697257', 'https://robohash.org/sintadistinctio.png?size=50x50&set=set1', 'Pre-emptive holistic alliance', '1982-02-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jonie Corsan', 'jcorsany', 'jcorsany@indiatimes.com', 'nN4=gJF%f7X', '7559313640', 'https://robohash.org/atdoloresquas.png?size=50x50&set=set1', 'Implemented heuristic hub', '1971-02-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Morie Moulsdale', 'mmoulsdalez', 'mmoulsdalez@typepad.com', 'sM9''1eCDVrhq0$', '3619148003', 'https://robohash.org/architectoenimnostrum.png?size=50x50&set=set1', 'Visionary 24 hour matrices', '1976-05-17', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rosemonde Fearon', 'rfearon10', 'rfearon10@jugem.jp', 'bP4=x&`nV', '2746713918', 'https://robohash.org/quasimolestiaevoluptatem.png?size=50x50&set=set1', 'Optional bifurcated complexity', '1971-02-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gretal Ollenbuttel', 'gollenbuttel11', 'gollenbuttel11@arizona.edu', 'pN8)0iD_<&=L', '5534512262', 'https://robohash.org/etpariaturquia.png?size=50x50&set=set1', 'User-centric bi-directional infrastructure', '1991-01-13', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Leontine Dunsire', 'ldunsire12', 'ldunsire12@army.mil', 'xD6/OTc*Matd', '5847595369', 'https://robohash.org/temporibusiddolorem.png?size=50x50&set=set1', 'Robust global parallelism', '1995-05-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Robert Monger', 'rmonger13', 'rmonger13@nhs.uk', 'mQ6.<>GV8|)FNK', '1164774069', 'https://robohash.org/vitaeestlibero.png?size=50x50&set=set1', 'Secured 5th generation project', '1994-06-13', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Linn Lamba', 'llamba14', 'llamba14@1und1.de', 'oA9&GhXJu<1', '6573229684', 'https://robohash.org/quiavelnostrum.png?size=50x50&set=set1', 'Triple-buffered human-resource encoding', '1998-09-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ame Mesias', 'amesias15', 'amesias15@wordpress.org', 'nB5(v*HwVwO?{', '5096308714', 'https://robohash.org/iustoiurenesciunt.png?size=50x50&set=set1', 'Phased zero defect strategy', '2000-05-07', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ford Vautre', 'fvautre16', 'fvautre16@amazon.co.jp', 'aI7$YqbvUW', '5668049488', 'https://robohash.org/verovoluptassint.png?size=50x50&set=set1', 'Multi-lateral real-time approach', '1990-12-28', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Wileen Cawker', 'wcawker17', 'wcawker17@trellian.com', 'qI6/Q|u9n%,9n%FC', '3713162095', 'https://robohash.org/minusquasinventore.png?size=50x50&set=set1', 'Inverse bifurcated standardization', '1974-09-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ellary Edgson', 'eedgson18', 'eedgson18@google.co.jp', 'wT8<BmEcWtC>$', '4516311215', 'https://robohash.org/quisnequeadipisci.png?size=50x50&set=set1', 'Programmable multi-state concept', '2004-03-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sydney Wraggs', 'swraggs19', 'swraggs19@ustream.tv', 'gZ1~7R{''M@<4A*M4', '7192710050', 'https://robohash.org/delectusliberosapiente.png?size=50x50&set=set1', 'Cross-platform impactful complexity', '1987-06-27', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Marlowe Flinn', 'mflinn1a', 'mflinn1a@surveymonkey.com', 'oH4*gyZ}', '8531215194', 'https://robohash.org/consectetureumet.png?size=50x50&set=set1', 'Proactive eco-centric analyzer', '2002-10-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sada Aronsohn', 'saronsohn1b', 'saronsohn1b@reference.com', 'qW2+\.~\ks@Y~3f', '7083227971', 'https://robohash.org/commodiexplicabovoluptas.png?size=50x50&set=set1', 'Object-based heuristic benchmark', '1980-12-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gianina McCullouch', 'gmccullouch1c', 'gmccullouch1c@instagram.com', 'kW0.S7yh', '3151466612', 'https://robohash.org/aliquamestprovident.png?size=50x50&set=set1', 'Reduced attitude-oriented model', '2001-12-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Arabel Mochan', 'amochan1d', 'amochan1d@npr.org', 'sM0$6jXQ)WCM&$', '4783633158', 'https://robohash.org/quasnonullam.png?size=50x50&set=set1', 'Centralized national budgetary management', '1978-01-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Uriel McMoyer', 'umcmoyer1e', 'umcmoyer1e@unblog.fr', 'nV7<xgRs$WVikm4', '8092929368', 'https://robohash.org/laudantiumimpeditneque.png?size=50x50&set=set1', 'Quality-focused object-oriented workforce', '1974-03-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Flo Bowld', 'fbowld1f', 'fbowld1f@creativecommons.org', 'zS2~g*<`', '1085374170', 'https://robohash.org/noneasuscipit.png?size=50x50&set=set1', 'De-engineered attitude-oriented utilisation', '1973-09-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Debor Torbeck', 'dtorbeck1g', 'dtorbeck1g@ucla.edu', 'xW2|is7&<5', '4055463772', 'https://robohash.org/rerumhicomnis.png?size=50x50&set=set1', 'Future-proofed bottom-line capability', '1979-11-28', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Pooh Dobbing', 'pdobbing1h', 'pdobbing1h@sbwire.com', 'dH4~(/8,Nr,''jUR', '3551192931', 'https://robohash.org/eaautemmagnam.png?size=50x50&set=set1', 'User-centric incremental encryption', '2003-03-11', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lesley Ninnis', 'lninnis1i', 'lninnis1i@alibaba.com', 'yY7~tZV8LNG', '8643720587', 'https://robohash.org/earumsapienteasperiores.png?size=50x50&set=set1', 'Ergonomic dynamic access', '2001-03-27', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Carrissa Terram', 'cterram1j', 'cterram1j@bloomberg.com', 'jS3<d`8cZe7d0Z3M', '5999947314', 'https://robohash.org/eiusfugasapiente.png?size=50x50&set=set1', 'Multi-lateral heuristic implementation', '1979-04-03', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Constanta Teasdale', 'cteasdale1k', 'cteasdale1k@telegraph.co.uk', 'dL7%o_,e/q', '3819546802', 'https://robohash.org/quasiomnisearum.png?size=50x50&set=set1', 'Advanced regional knowledge user', '1997-10-20', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rossy Sacher', 'rsacher1l', 'rsacher1l@mediafire.com', 'rE6?XvSL{94@', '3539180270', 'https://robohash.org/adquimollitia.png?size=50x50&set=set1', 'Organized multi-state core', '2001-09-26', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tamarra Blakeslee', 'tblakeslee1m', 'tblakeslee1m@washington.edu', 'pR5(""Krc6|XpMq', '3486493100', 'https://robohash.org/voluptatesipsaet.png?size=50x50&set=set1', 'Synergized high-level capability', '1976-08-13', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Maximilianus Tuffell', 'mtuffell1n', 'mtuffell1n@unblog.fr', 'fX8!f8&}@', '7174846402', 'https://robohash.org/illonumquamnobis.png?size=50x50&set=set1', 'Optimized directional productivity', '1986-01-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lindon Sutherel', 'lsutherel1o', 'lsutherel1o@cdbaby.com', 'wU4)hq{+', '7576989846', 'https://robohash.org/quasiconsecteturofficiis.png?size=50x50&set=set1', 'Public-key bi-directional initiative', '1990-08-17', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Zsazsa Lind', 'zlind1p', 'zlind1p@bizjournals.com', 'rL0{bnM?tX\V_JW0', '8846804627', 'https://robohash.org/quiaauteius.png?size=50x50&set=set1', 'Fundamental radical archive', '1982-04-28', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Katina McKleod', 'kmckleod1q', 'kmckleod1q@who.int', 'nU6`+%OY5CVK', '2532422288', 'https://robohash.org/pariatursaepeminus.png?size=50x50&set=set1', 'Quality-focused executive infrastructure', '1995-04-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lyndsey Grangier', 'lgrangier1r', 'lgrangier1r@is.gd', 'xM4,Kxa{WThd', '9378446152', 'https://robohash.org/velarchitectoet.png?size=50x50&set=set1', 'Total zero defect toolset', '1999-04-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Maudie Selesnick', 'mselesnick1s', 'mselesnick1s@altervista.org', 'bT6+FG2d', '6191431825', 'https://robohash.org/voluptatemconsequaturest.png?size=50x50&set=set1', 'Extended actuating product', '1999-01-11', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Brandyn Andreolli', 'bandreolli1t', 'bandreolli1t@chicagotribune.com', 'yN9*HdMRo5{Fl~3\', '8475743743', 'https://robohash.org/exercitationemaperiamaut.png?size=50x50&set=set1', 'Optional hybrid synergy', '1991-04-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Freddy Dukelow', 'fdukelow1u', 'fdukelow1u@blogtalkradio.com', 'qI4+C9}f!O', '7686822618', 'https://robohash.org/seditaqueaut.png?size=50x50&set=set1', 'Business-focused client-driven adapter', '1997-03-13', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Patrica Reschke', 'preschke1v', 'preschke1v@bandcamp.com', 'hN0\}yLffo', '3027915830', 'https://robohash.org/quisedest.png?size=50x50&set=set1', 'Re-contextualized methodical database', '1997-02-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Giustina Pennings', 'gpennings1w', 'gpennings1w@amazon.com', 'eZ4`xPvyp.)4M', '7924847810', 'https://robohash.org/suscipitcorruptiid.png?size=50x50&set=set1', 'Customizable holistic function', '1994-08-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dacia Tessier', 'dtessier1x', 'dtessier1x@redcross.org', 'nJ3=fWeM3Y6x?Y)', '5062293470', 'https://robohash.org/natusexharum.png?size=50x50&set=set1', 'Expanded holistic protocol', '1975-06-11', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Mary Bevar', 'mbevar1y', 'mbevar1y@histats.com', 'bC1|hWs+8JbXM', '5018615573', 'https://robohash.org/debitisrecusandaeasperiores.png?size=50x50&set=set1', 'Decentralized bottom-line hierarchy', '1980-12-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Aggi Banes', 'abanes1z', 'abanes1z@cmu.edu', 'rH5&G0MQ!IJPBLE', '4221500210', 'https://robohash.org/nostrumsedquas.png?size=50x50&set=set1', 'Re-contextualized next generation moderator', '1985-10-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rosie Kinkead', 'rkinkead20', 'rkinkead20@boston.com', 'oQ7",E\JO', '5616879122', 'https://robohash.org/enimnonet.png?size=50x50&set=set1', 'Polarised mission-critical productivity', '2002-03-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Michael Thaxton', 'mthaxton21', 'mthaxton21@nymag.com', 'cZ7''Wq05$', '9424277717', 'https://robohash.org/blanditiisexpeditanon.png?size=50x50&set=set1', 'Optional eco-centric infrastructure', '1972-03-20', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bevin Joontjes', 'bjoontjes22', 'bjoontjes22@meetup.com', 'yI4$uLp2p+x\H', '5689115873', 'https://robohash.org/doloraccusamusaut.png?size=50x50&set=set1', 'Synergized motivating forecast', '1980-07-24', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Aurore Ashbe', 'aashbe23', 'aashbe23@angelfire.com', 'iS4\e<>c7', '2496250901', 'https://robohash.org/dolorpariaturvoluptates.png?size=50x50&set=set1', 'Cross-platform didactic framework', '1995-06-19', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Graeme Short', 'gshort24', 'gshort24@surveymonkey.com', 'iK2$r(?!?', '5388709464', 'https://robohash.org/doloresconsequaturin.png?size=50x50&set=set1', 'Profound eco-centric definition', '2004-12-31', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Christel Darlington', 'cdarlington25', 'cdarlington25@flickr.com', 'qL8,Cmy>\&RRh', '2257629964', 'https://robohash.org/modireprehenderitin.png?size=50x50&set=set1', 'Organic intangible pricing structure', '1989-09-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Hilly Caplen', 'hcaplen26', 'hcaplen26@mozilla.org', 'rZ3(dJZ?n`)', '9437288132', 'https://robohash.org/utharumsed.png?size=50x50&set=set1', 'Multi-channelled 24/7 encryption', '1978-10-19', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lavinie Barras', 'lbarras27', 'lbarras27@wikipedia.org', 'jV5`HX>Q2bV*', '3471321640', 'https://robohash.org/eiusnatuset.png?size=50x50&set=set1', 'Quality-focused 4th generation groupware', '1983-02-07', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gretchen Barrows', 'gbarrows28', 'gbarrows28@census.gov', 'fK8|JnR~m', '6736115767', 'https://robohash.org/vitaesitut.png?size=50x50&set=set1', 'Front-line solution-oriented info-mediaries', '1984-10-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Adaline Presnall', 'apresnall29', 'apresnall29@drupal.org', 'kX6@9zDr,', '4324594629', 'https://robohash.org/excepturiofficiisvoluptas.png?size=50x50&set=set1', 'Focused needs-based parallelism', '1980-05-31', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ruthann Mogra', 'rmogra2a', 'rmogra2a@csmonitor.com', 'eM6!tWb<4s', '8271430365', 'https://robohash.org/sintdictarepudiandae.png?size=50x50&set=set1', 'Multi-tiered non-volatile knowledge base', '1986-11-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Phaidra Hryniewicz', 'phryniewicz2b', 'phryniewicz2b@topsy.com', 'oY4=C9*HG', '1394026707', 'https://robohash.org/voluptatesvoluptatibusomnis.png?size=50x50&set=set1', 'Upgradable bandwidth-monitored website', '1976-11-18', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rycca Rosina', 'rrosina2c', 'rrosina2c@feedburner.com', 'rW6`>phD/oh2j_', '5807632688', 'https://robohash.org/rerummodivoluptatum.png?size=50x50&set=set1', 'User-friendly 4th generation functionalities', '1993-09-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Hulda Noke', 'hnoke2d', 'hnoke2d@dagondesign.com', 'nV1>egnKdTT', '4516151598', 'https://robohash.org/suscipitvoluptatemquis.png?size=50x50&set=set1', 'Object-based 3rd generation array', '1982-07-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tori Rudd', 'trudd2e', 'trudd2e@opensource.org', 'zL4.''(2BOF(<jU=n', '5115095188', 'https://robohash.org/molestiaeasperioresratione.png?size=50x50&set=set1', 'Assimilated multimedia structure', '1972-01-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bartholemy MacAfee', 'bmacafee2f', 'bmacafee2f@lycos.com', 'pJ5|qi5PE2', '4655043424', 'https://robohash.org/itaquequidemnulla.png?size=50x50&set=set1', 'Innovative 24 hour Graphical User Interface', '1978-06-17', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dreddy Ingon', 'dingon2g', 'dingon2g@microsoft.com', 'oJ6~qAsIa', '3743871662', 'https://robohash.org/doloresetillo.png?size=50x50&set=set1', 'Focused coherent adapter', '1995-06-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Boot Holson', 'bholson2h', 'bholson2h@163.com', 'kZ8_3.Djh1@yhJ', '9847528321', 'https://robohash.org/nihilautin.png?size=50x50&set=set1', 'Automated zero administration workforce', '2001-04-14', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jamie Draaisma', 'jdraaisma2i', 'jdraaisma2i@elegantthemes.com', 'kU7`qOjf(=~Q', '5888717116', 'https://robohash.org/delenitiquodtenetur.png?size=50x50&set=set1', 'Total full-range challenge', '1996-08-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Maxi Fomichkin', 'mfomichkin2j', 'mfomichkin2j@ehow.com', 'iC0@gzPA', '8468968616', 'https://robohash.org/quiquidemut.png?size=50x50&set=set1', 'Distributed eco-centric hub', '1999-09-11', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Laure Jurczak', 'ljurczak2k', 'ljurczak2k@loc.gov', 'xA3|11TeE}u<V`', '3314341685', 'https://robohash.org/totameossed.png?size=50x50&set=set1', 'Triple-buffered logistical benchmark', '1997-07-14', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tedd Tolson', 'ttolson2l', 'ttolson2l@whitehouse.gov', 'pN4*g6kQ/DK', '6665942956', 'https://robohash.org/utidmolestias.png?size=50x50&set=set1', 'Grass-roots even-keeled parallelism', '1986-10-05', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Svend Heibel', 'sheibel2m', 'sheibel2m@cisco.com', 'uF7''GN7$', '7062913225', 'https://robohash.org/voluptasquiquia.png?size=50x50&set=set1', 'Persevering contextually-based analyzer', '1982-12-14', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kelwin Stubs', 'kstubs2n', 'kstubs2n@pcworld.com', 'yL8&%M\jP3ab''G6', '3547829927', 'https://robohash.org/quoconsecteturiure.png?size=50x50&set=set1', 'Open-architected fresh-thinking application', '1983-02-28', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Conn Focke', 'cfocke2o', 'cfocke2o@rambler.ru', 'xW5}nmgid2{9G', '9006482752', 'https://robohash.org/consequaturisteperferendis.png?size=50x50&set=set1', 'Reduced multi-tasking local area network', '1974-05-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Clerc Chancellor', 'cchancellor2p', 'cchancellor2p@scribd.com', 'lH6!+T8ZG7*', '2561947245', 'https://robohash.org/consequunturassumendaet.png?size=50x50&set=set1', 'Progressive transitional task-force', '1981-12-20', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Eberhard Hampe', 'ehampe2q', 'ehampe2q@globo.com', 'jZ5)X4dlYT&/|`', '4893188827', 'https://robohash.org/utpossimusest.png?size=50x50&set=set1', 'Multi-tiered analyzing internet solution', '1976-02-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Janessa Kort', 'jkort2r', 'jkort2r@nyu.edu', 'iM8''dXU}K4', '6779650070', 'https://robohash.org/omnisnesciuntquia.png?size=50x50&set=set1', 'Exclusive motivating parallelism', '1987-12-16', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Yanaton Gothard', 'ygothard2s', 'ygothard2s@wp.com', 'sT4,%.)v,k''sD', '5833087701', 'https://robohash.org/vitaeteneturin.png?size=50x50&set=set1', 'Re-contextualized tertiary concept', '1978-11-08', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Elli Raybould', 'eraybould2t', 'eraybould2t@buzzfeed.com', 'bC1<*S}kMs*', '5227170689', 'https://robohash.org/autemquierror.png?size=50x50&set=set1', 'Upgradable uniform projection', '1997-02-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ward Seldner', 'wseldner2u', 'wseldner2u@dagondesign.com', 'yY7~)|5A8(', '6542091065', 'https://robohash.org/eosconsequaturquibusdam.png?size=50x50&set=set1', 'Exclusive hybrid conglomeration', '1998-12-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Marybelle Leuty', 'mleuty2v', 'mleuty2v@comsenz.com', 'kB6+"Y.B5kTBl''`y', '5981262708', 'https://robohash.org/vitaeetplaceat.png?size=50x50&set=set1', 'Total tertiary secured line', '1997-10-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kort Horwell', 'khorwell2w', 'khorwell2w@craigslist.org', 'pG2)"gr+8z_SaLh', '5064926551', 'https://robohash.org/dolorconsequunturcupiditate.png?size=50x50&set=set1', 'Function-based intermediate extranet', '1989-02-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Adam Koubu', 'akoubu2x', 'akoubu2x@google.com.au', 'bI4=L%cr*pZ3', '3569021317', 'https://robohash.org/eumnonquia.png?size=50x50&set=set1', 'Operative static monitoring', '2000-04-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bebe Frame', 'bframe2y', 'bframe2y@usgs.gov', 'qP4\6m8pp', '8017939906', 'https://robohash.org/totamfacerefugiat.png?size=50x50&set=set1', 'Team-oriented uniform function', '1988-06-11', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rivi Ellingham', 'rellingham2z', 'rellingham2z@chronoengine.com', 'hK2\)D_(Y,NhT', '9511163709', 'https://robohash.org/rempossimusfugit.png?size=50x50&set=set1', 'Team-oriented bifurcated protocol', '2003-03-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gilligan Quayle', 'gquayle30', 'gquayle30@bing.com', 'eK8.ijG6"', '1792000746', 'https://robohash.org/hicimpeditomnis.png?size=50x50&set=set1', 'Pre-emptive intermediate time-frame', '1987-02-22', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Nelie Fortnum', 'nfortnum31', 'nfortnum31@fotki.com', 'fC0!Rr%a)', '8163232630', 'https://robohash.org/liberoatrem.png?size=50x50&set=set1', 'Mandatory disintermediate architecture', '1981-07-26', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ilise Revett', 'irevett32', 'irevett32@exblog.jp', 'cR5+wH1erEdGz=', '4109612432', 'https://robohash.org/consequunturdistinctioasperiores.png?size=50x50&set=set1', 'Adaptive eco-centric forecast', '1992-09-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Annamarie Old', 'aold33', 'aold33@drupal.org', 'yP8?m(?o+', '3008536399', 'https://robohash.org/accusantiumutqui.png?size=50x50&set=set1', 'Customer-focused bottom-line synergy', '1985-11-22', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kenna Stegers', 'kstegers34', 'kstegers34@hatena.ne.jp', 'gJ0)q8~(KWqIe*z', '7375802185', 'https://robohash.org/ullamquaeest.png?size=50x50&set=set1', 'Future-proofed asymmetric implementation', '1989-11-08', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lari Lindop', 'llindop35', 'llindop35@jalbum.net', 'xE6@LO$Txi', '8227207667', 'https://robohash.org/velitmollitiaconsequatur.png?size=50x50&set=set1', 'Enterprise-wide interactive instruction set', '1974-05-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Aurthur Vardon', 'avardon36', 'avardon36@webnode.com', 'nR0@,Mj{{', '4961364555', 'https://robohash.org/voluptatumdolorumbeatae.png?size=50x50&set=set1', 'Front-line zero defect product', '2001-01-07', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Nolly Gonthier', 'ngonthier37', 'ngonthier37@a8.net', 'xM4|uy$Tzd>mFV', '2423819656', 'https://robohash.org/impeditatsit.png?size=50x50&set=set1', 'Right-sized executive capability', '1994-07-26', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dorena Smillie', 'dsmillie38', 'dsmillie38@artisteer.com', 'uP3*Y1&hB524O/', '3977883476', 'https://robohash.org/excepturiperspiciatisnesciunt.png?size=50x50&set=set1', 'Diverse 24 hour support', '1996-05-24', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dougy Carrabott', 'dcarrabott39', 'dcarrabott39@indiatimes.com', 'uL3>JSQey66{.Pi', '9318367882', 'https://robohash.org/quiaveroeos.png?size=50x50&set=set1', 'Multi-lateral 6th generation project', '1983-05-28', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gisela Baldini', 'gbaldini3a', 'gbaldini3a@oracle.com', 'jK3>"h\g2C', '2474272317', 'https://robohash.org/commodialiassapiente.png?size=50x50&set=set1', 'Visionary zero tolerance internet solution', '1993-11-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Culver Burrett', 'cburrett3b', 'cburrett3b@theglobeandmail.com', 'gI2,OTyGn0>Td=', '6072116921', 'https://robohash.org/sedautautem.png?size=50x50&set=set1', 'Networked fresh-thinking matrix', '1992-12-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Farrah Tremblay', 'ftremblay3c', 'ftremblay3c@hexun.com', 'aE2_D(B8$>', '5006437265', 'https://robohash.org/voluptatemidvero.png?size=50x50&set=set1', 'Managed 24/7 structure', '1990-05-07', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Theresa Morfell', 'tmorfell3d', 'tmorfell3d@jugem.jp', 'qK0#9gth', '4714246259', 'https://robohash.org/consecteturreiciendiscorrupti.png?size=50x50&set=set1', 'Pre-emptive background time-frame', '1992-04-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kimbell Van Der Weedenburg', 'kvan3e', 'kvan3e@aboutads.info', 'qV9~%KBGc', '2966575215', 'https://robohash.org/anumquamassumenda.png?size=50x50&set=set1', 'Visionary methodical orchestration', '1987-08-31', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jerrilee Haselup', 'jhaselup3f', 'jhaselup3f@shareasale.com', 'fN1_K{<ETxx$', '6253799526', 'https://robohash.org/quaeratpossimuscommodi.png?size=50x50&set=set1', 'Expanded zero defect definition', '1996-06-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Shauna Kernan', 'skernan3g', 'skernan3g@mail.ru', 'eO4>|1{EI&wZHZ*r', '6571188777', 'https://robohash.org/atquequismolestiae.png?size=50x50&set=set1', 'Function-based asymmetric process improvement', '1991-12-10', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Robert Wilsone', 'rwilsone3h', 'rwilsone3h@nydailynews.com', 'iO7~@X1M}(K', '7629675640', 'https://robohash.org/ipsaetaut.png?size=50x50&set=set1', 'Re-engineered actuating intranet', '2003-01-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Udale Hospital', 'uhospital3i', 'uhospital3i@spotify.com', 'wW2&ieA`9', '6812265464', 'https://robohash.org/eteosquos.png?size=50x50&set=set1', 'Reduced explicit implementation', '1976-09-25', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Mariquilla Cavozzi', 'mcavozzi3j', 'mcavozzi3j@phpbb.com', 'uM4"fE_d>', '9563298510', 'https://robohash.org/vitaecorruptinatus.png?size=50x50&set=set1', 'Down-sized zero administration concept', '1995-05-25', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ruprecht Narrie', 'rnarrie3k', 'rnarrie3k@cloudflare.com', 'rB3<$!F<%z>FVNZ+', '1192797287', 'https://robohash.org/idquasialiquid.png?size=50x50&set=set1', 'Assimilated object-oriented benchmark', '1983-08-15', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kristofer Landall', 'klandall3l', 'klandall3l@cyberchimps.com', 'xR2+''($6''U4', '9908039942', 'https://robohash.org/abexplicabosed.png?size=50x50&set=set1', 'Intuitive human-resource open system', '2000-09-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sula Bridewell', 'sbridewell3m', 'sbridewell3m@gizmodo.com', 'pS4''@t6WKdB?Q.2', '6999894207', 'https://robohash.org/asperioresnisinumquam.png?size=50x50&set=set1', 'Triple-buffered secondary policy', '1971-07-12', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Conny Schankelborg', 'cschankelborg3n', 'cschankelborg3n@barnesandnoble.com', 'cK4?Vl{%QW"gFz', '2863558834', 'https://robohash.org/eaqueenimprovident.png?size=50x50&set=set1', 'Distributed systemic matrices', '1986-05-21', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Aron Bonafant', 'abonafant3o', 'abonafant3o@google.es', 'uG5+p`2ESr', '2304907686', 'https://robohash.org/illofugiatvelit.png?size=50x50&set=set1', 'Persistent mission-critical secured line', '1973-01-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Pat Lyddon', 'plyddon3p', 'plyddon3p@upenn.edu', 'kR9{z4cpkD', '8011315198', 'https://robohash.org/estprovidentillum.png?size=50x50&set=set1', 'Diverse upward-trending concept', '1995-01-22', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Nellie Bolens', 'nbolens3q', 'nbolens3q@businessinsider.com', 'wE5}&P1qlDA', '8073133793', 'https://robohash.org/reiciendislaboriosamlibero.png?size=50x50&set=set1', 'Persevering foreground moratorium', '1978-12-27', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cecilio Dhillon', 'cdhillon3r', 'cdhillon3r@thetimes.co.uk', 'aE9>fw~yP?Dj', '7472134256', 'https://robohash.org/delenitideseruntet.png?size=50x50&set=set1', 'Managed maximized data-warehouse', '1980-01-26', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Wyatt Doy', 'wdoy3s', 'wdoy3s@theguardian.com', 'vW1/Gbwz', '4916501556', 'https://robohash.org/namexpeditadolorem.png?size=50x50&set=set1', 'Enhanced optimizing knowledge user', '1984-09-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Herve Childes', 'hchildes3t', 'hchildes3t@sciencedirect.com', 'pY1\6mbH"L''', '5364304361', 'https://robohash.org/consequunturunderepellendus.png?size=50x50&set=set1', 'Monitored zero defect matrices', '1986-11-20', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ramsey Andreutti', 'randreutti3u', 'randreutti3u@imageshack.us', 'oI6#0v5#P|', '3217489971', 'https://robohash.org/corruptietaperiam.png?size=50x50&set=set1', 'Realigned web-enabled policy', '1993-03-10', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Darbie Iacopetti', 'diacopetti3v', 'diacopetti3v@dailymotion.com', 'sY0*16$?', '7339651828', 'https://robohash.org/animilaborumvoluptatibus.png?size=50x50&set=set1', 'Organic systematic open system', '1974-01-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rayshell Kitman', 'rkitman3w', 'rkitman3w@narod.ru', 'rP8\oNh%', '3143430906', 'https://robohash.org/doloribussitplaceat.png?size=50x50&set=set1', 'Organic web-enabled contingency', '1977-11-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sigmund Cancellor', 'scancellor3x', 'scancellor3x@marriott.com', 'xR0,1grk=,D', '2972177016', 'https://robohash.org/dolorexplicabomollitia.png?size=50x50&set=set1', 'Compatible methodical interface', '1986-07-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Zebedee Greder', 'zgreder3y', 'zgreder3y@alibaba.com', 'yC4%@q7br+', '1905951997', 'https://robohash.org/dolorullamin.png?size=50x50&set=set1', 'Up-sized logistical conglomeration', '1989-03-05', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Hannie Marjot', 'hmarjot3z', 'hmarjot3z@vk.com', 'zK4>\5=_i(\x#&>', '1062322613', 'https://robohash.org/dignissimossitut.png?size=50x50&set=set1', 'Versatile tangible strategy', '1985-12-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Hardy Morbey', 'hmorbey40', 'hmorbey40@netlog.com', 'eB5>qG0lkPq`', '7663677091', 'https://robohash.org/velitatqueminus.png?size=50x50&set=set1', 'Persevering regional parallelism', '1988-04-20', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Yankee Dunleavy', 'ydunleavy41', 'ydunleavy41@guardian.co.uk', 'wV1=YnF?1q|UUW@l', '1256192054', 'https://robohash.org/blanditiisasperioresperspiciatis.png?size=50x50&set=set1', 'Re-contextualized well-modulated migration', '1982-12-25', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ugo MacCole', 'umaccole42', 'umaccole42@japanpost.jp', 'mJ7@eHpDj4i', '5176813439', 'https://robohash.org/fugiatcupiditatequi.png?size=50x50&set=set1', 'Persistent neutral function', '1998-06-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Audrey Ploughwright', 'aploughwright43', 'aploughwright43@ucoz.ru', 'xP3"&\B?$Ixy', '9412467905', 'https://robohash.org/quamrerumest.png?size=50x50&set=set1', 'Customer-focused dedicated function', '1979-07-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Christoph Jervois', 'cjervois44', 'cjervois44@jiathis.com', 'aK7+@ViE5PWa$/', '6642863515', 'https://robohash.org/exsedet.png?size=50x50&set=set1', 'Business-focused logistical knowledge user', '1996-10-13', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Genny Minton', 'gminton45', 'gminton45@hexun.com', 'rZ2#*1|o>BrM)', '3649872703', 'https://robohash.org/vitaeutipsam.png?size=50x50&set=set1', 'Open-architected homogeneous methodology', '1975-12-14', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Elisha Butterley', 'ebutterley46', 'ebutterley46@newsvine.com', 'fS6*/hQXs', '2936853551', 'https://robohash.org/nihilquossunt.png?size=50x50&set=set1', 'Progressive full-range array', '1982-04-22', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gian Matczak', 'gmatczak47', 'gmatczak47@hao123.com', 'wH3{.Y<QJF/P', '8127170961', 'https://robohash.org/laboriosamdoloribusatque.png?size=50x50&set=set1', 'Monitored multi-tasking access', '1993-04-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Armin Cullagh', 'acullagh48', 'acullagh48@163.com', 'sE7%u|\m(H|!G!YO', '7767490412', 'https://robohash.org/laboriosamomniset.png?size=50x50&set=set1', 'Extended analyzing extranet', '2002-12-21', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Krishnah Seater', 'kseater49', 'kseater49@nydailynews.com', 'kQ0<y=j8S!eSM!', '4304500227', 'https://robohash.org/similiquenumquamautem.png?size=50x50&set=set1', 'Compatible holistic collaboration', '2004-03-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Madelon Thornborrow', 'mthornborrow4a', 'mthornborrow4a@blogtalkradio.com', 'qU5\#?f5$5O#', '5604489225', 'https://robohash.org/doloremquequivoluptatibus.png?size=50x50&set=set1', 'Multi-lateral client-server parallelism', '2001-06-27', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Mariam Fayer', 'mfayer4b', 'mfayer4b@cmu.edu', 'uH6$Hl6Om4', '4658227333', 'https://robohash.org/quiacommodiharum.png?size=50x50&set=set1', 'Profit-focused motivating groupware', '1987-03-26', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kai Spanton', 'kspanton4c', 'kspanton4c@umich.edu', 'hD3\@#L%YgI', '7026633304', 'https://robohash.org/reiciendisexoptio.png?size=50x50&set=set1', 'Synchronised system-worthy concept', '1992-08-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Wendi Nehls', 'wnehls4d', 'wnehls4d@cam.ac.uk', 'oK1@VYhjzi`X*', '8296016492', 'https://robohash.org/nobiseligendinon.png?size=50x50&set=set1', 'Grass-roots scalable parallelism', '1998-11-07', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Leticia Josephi', 'ljosephi4e', 'ljosephi4e@chicagotribune.com', 'aC8\WZ$yvwELqNt', '6265746209', 'https://robohash.org/totamsaepenumquam.png?size=50x50&set=set1', 'Reverse-engineered client-server info-mediaries', '2005-07-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rurik Darnody', 'rdarnody4f', 'rdarnody4f@toplist.cz', 'hC9.seo2OT', '2049784914', 'https://robohash.org/utvitaeofficia.png?size=50x50&set=set1', 'Enhanced client-driven interface', '1988-05-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Judie Simon', 'jsimon4g', 'jsimon4g@privacy.gov.au', 'yI7=oNg)hF_q>f', '4077733791', 'https://robohash.org/sintsitut.png?size=50x50&set=set1', 'Self-enabling dynamic database', '1991-01-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Muffin O''Bradane', 'mobradane4h', 'mobradane4h@twitter.com', 'dM8"QZWs8', '5786606654', 'https://robohash.org/doloribusomnissapiente.png?size=50x50&set=set1', 'Diverse bi-directional migration', '1977-07-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Agnese Heathcoat', 'aheathcoat4i', 'aheathcoat4i@discovery.com', 'dS9$<LdP', '8619199974', 'https://robohash.org/oditoccaecatiut.png?size=50x50&set=set1', 'Programmable cohesive solution', '1975-08-31', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gardy Ogilvie', 'gogilvie4j', 'gogilvie4j@engadget.com', 'vO2/u$lChBxNjD', '7637719145', 'https://robohash.org/doloremcorruptitemporibus.png?size=50x50&set=set1', 'Front-line 24/7 moratorium', '1978-07-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Otes Spary', 'ospary4k', 'ospary4k@cyberchimps.com', 'hC4#Fiq&kO', '2477493908', 'https://robohash.org/etoditoptio.png?size=50x50&set=set1', 'Distributed maximized capability', '1983-03-27', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jamal Satterfitt', 'jsatterfitt4l', 'jsatterfitt4l@ebay.co.uk', 'cA5?Sw!1', '6431201580', 'https://robohash.org/repudiandaearchitectofacere.png?size=50x50&set=set1', 'Front-line non-volatile alliance', '1976-02-05', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Verena Caroll', 'vcaroll4m', 'vcaroll4m@simplemachines.org', 'yG2#x9v2\+Jl', '5176746405', 'https://robohash.org/aspernaturfugafacilis.png?size=50x50&set=set1', 'Assimilated mobile workforce', '1981-03-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ketti Weavill', 'kweavill4n', 'kweavill4n@redcross.org', 'bB6$8T7{7\', '1296264952', 'https://robohash.org/utmagniperspiciatis.png?size=50x50&set=set1', 'Organic 4th generation framework', '1995-04-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ives Monkley', 'imonkley4o', 'imonkley4o@livejournal.com', 'zT7_Mk|+o2x', '7508781301', 'https://robohash.org/magnameumvoluptatibus.png?size=50x50&set=set1', 'Implemented neutral help-desk', '1995-09-29', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sharline Juggins', 'sjuggins4p', 'sjuggins4p@free.fr', 'dH3|k$s{', '7044034746', 'https://robohash.org/delectusculpaquae.png?size=50x50&set=set1', 'Reverse-engineered content-based model', '1996-09-29', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Marcelline Eames', 'meames4q', 'meames4q@geocities.jp', 'pD8)LxH32W2Ygn', '5142174274', 'https://robohash.org/odionihilharum.png?size=50x50&set=set1', 'Team-oriented solution-oriented circuit', '1974-08-18', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bird Ferne', 'bferne4r', 'bferne4r@google.com.br', 'wC5{b0zsR4m', '5039624080', 'https://robohash.org/laudantiumrepellendusfugit.png?size=50x50&set=set1', 'Compatible systematic array', '2000-05-21', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Heall Rattenbury', 'hrattenbury4s', 'hrattenbury4s@upenn.edu', 'oB6&xylpy', '6108504395', 'https://robohash.org/eteumvel.png?size=50x50&set=set1', 'Upgradable even-keeled definition', '1970-12-07', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Emilia Traise', 'etraise4t', 'etraise4t@exblog.jp', 'fI2{jdaxZ''$H', '6178307588', 'https://robohash.org/fugiatquiconsequatur.png?size=50x50&set=set1', 'Compatible solution-oriented archive', '1978-10-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lucie Haggerston', 'lhaggerston4u', 'lhaggerston4u@umn.edu', 'iF3``Y~fCZ8n~5d>', '2401965874', 'https://robohash.org/cumquenatusautem.png?size=50x50&set=set1', 'Cross-group logistical emulation', '1987-03-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sibylle Atkin', 'satkin4v', 'satkin4v@ezinearticles.com', 'tN7=R&=g9p', '3575000017', 'https://robohash.org/sequivoluptatemunde.png?size=50x50&set=set1', 'Assimilated heuristic open system', '1998-07-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Anna-maria Hewlings', 'ahewlings4w', 'ahewlings4w@eepurl.com', 'sJ9$w%86', '7613906942', 'https://robohash.org/sitipsumab.png?size=50x50&set=set1', 'Expanded multi-tasking software', '1971-02-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Devora Lantaff', 'dlantaff4x', 'dlantaff4x@slate.com', 'lS4''67K3K040', '9135420951', 'https://robohash.org/etullamet.png?size=50x50&set=set1', 'Streamlined value-added focus group', '1989-01-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bethanne Gaukrodge', 'bgaukrodge4y', 'bgaukrodge4y@yandex.ru', 'tW5@o6+2Keq)8o', '8382344582', 'https://robohash.org/errordoloremimpedit.png?size=50x50&set=set1', 'Realigned composite infrastructure', '2001-08-21', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Juieta Dicty', 'jdicty4z', 'jdicty4z@msu.edu', 'cX9!q8=ZEtjKmHZ', '8358204959', 'https://robohash.org/modiofficiaut.png?size=50x50&set=set1', 'Robust holistic leverage', '1999-03-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bree Ambrosoli', 'bambrosoli50', 'bambrosoli50@alexa.com', 'cA4_g7))`_M6L{W.', '8492994862', 'https://robohash.org/magnamvoluptatemaut.png?size=50x50&set=set1', 'Switchable contextually-based support', '1971-02-03', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Traver Graysmark', 'tgraysmark51', 'tgraysmark51@stumbleupon.com', 'gQ4?nN!DfJ@6zCZ', '5238711721', 'https://robohash.org/quovoluptatemrerum.png?size=50x50&set=set1', 'Object-based radical neural-net', '1986-01-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sergent Terese', 'sterese52', 'sterese52@moonfruit.com', 'zO0/&.kUR|NtXjP', '2928842515', 'https://robohash.org/velautemvoluptas.png?size=50x50&set=set1', 'Down-sized clear-thinking moratorium', '1988-11-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cristionna Avramovsky', 'cavramovsky53', 'cavramovsky53@tripod.com', 'pG1{Qbp!/98Wy8,', '6918054967', 'https://robohash.org/autnondeleniti.png?size=50x50&set=set1', 'Innovative zero administration frame', '1986-03-29', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Marthe Wickerson', 'mwickerson54', 'mwickerson54@unblog.fr', 'yN2`}U5mvNk', '6795475720', 'https://robohash.org/optiodolorumeum.png?size=50x50&set=set1', 'Visionary multi-state middleware', '1983-11-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Marylou Sepey', 'msepey55', 'msepey55@fotki.com', 'hB1~t4%s$*cyp', '9789591168', 'https://robohash.org/eaquefugitvelit.png?size=50x50&set=set1', 'Expanded mobile customer loyalty', '2000-04-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kincaid Paddon', 'kpaddon56', 'kpaddon56@yolasite.com', 'pR6%C!r{||', '3781252420', 'https://robohash.org/quivoluptatumvel.png?size=50x50&set=set1', 'Implemented directional toolset', '1991-03-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Malina Drejer', 'mdrejer57', 'mdrejer57@feedburner.com', 'pK2#J.ZLA1YZ*', '5136291401', 'https://robohash.org/reiciendisnecessitatibusquidem.png?size=50x50&set=set1', 'Public-key static complexity', '1974-02-07', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jacquette MacKowle', 'jmackowle58', 'jmackowle58@google.ru', 'pV4/J<Ok168E(', '1066062609', 'https://robohash.org/quianihilfugit.png?size=50x50&set=set1', 'Cross-group optimizing paradigm', '1997-12-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Quillan Fernehough', 'qfernehough59', 'qfernehough59@blogtalkradio.com', 'nC6=zZKY,m3sFM', '3563214447', 'https://robohash.org/harumutrepellendus.png?size=50x50&set=set1', 'Operative global encryption', '2003-10-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Brenn Beddard', 'bbeddard5a', 'bbeddard5a@opera.com', 'yO1{Ia{N1MrO', '6843102584', 'https://robohash.org/perferendisdoloresnemo.png?size=50x50&set=set1', 'Cross-platform human-resource structure', '1976-07-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Almeria Whyman', 'awhyman5b', 'awhyman5b@oracle.com', 'zI0#i,IAilYL', '1123125341', 'https://robohash.org/earumquia.png?size=50x50&set=set1', 'Optimized local approach', '1994-07-24', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jocko Gerrets', 'jgerrets5c', 'jgerrets5c@irs.gov', 'eB0}BLIrD', '1208771495', 'https://robohash.org/velitquiafuga.png?size=50x50&set=set1', 'Mandatory scalable projection', '1978-04-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Trumaine Peschet', 'tpeschet5d', 'tpeschet5d@wufoo.com', 'nD3=m4y3Kvio', '9008743994', 'https://robohash.org/aliquidmaioresquis.png?size=50x50&set=set1', 'Optional discrete orchestration', '1992-07-31', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Phillida Kuhnel', 'pkuhnel5e', 'pkuhnel5e@jimdo.com', 'mQ6&Tj~h,bVf', '8812833930', 'https://robohash.org/laborumutquia.png?size=50x50&set=set1', 'Customer-focused static orchestration', '1990-07-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rickert Olexa', 'rolexa5f', 'rolexa5f@deliciousdays.com', 'iF3&''VHz0dOlv', '4956158380', 'https://robohash.org/quibusdamtemporibusnihil.png?size=50x50&set=set1', 'De-engineered demand-driven leverage', '1974-04-12', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Horatius Ganford', 'hganford5g', 'hganford5g@desdev.cn', 'iW3%om0I9n*Pg', '5639637480', 'https://robohash.org/dolorumrerumtempora.png?size=50x50&set=set1', 'Versatile 3rd generation knowledge user', '1973-12-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Wakefield Maskew', 'wmaskew5h', 'wmaskew5h@huffingtonpost.com', 'rD6!OEDi@', '3029327728', 'https://robohash.org/temporanullaautem.png?size=50x50&set=set1', 'Phased analyzing initiative', '1974-10-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Huntley Casero', 'hcasero5i', 'hcasero5i@utexas.edu', 'tU3*N<BZ', '8689818200', 'https://robohash.org/consequatursolutaqui.png?size=50x50&set=set1', 'Object-based modular moderator', '1990-12-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ingamar Gricewood', 'igricewood5j', 'igricewood5j@wunderground.com', 'nK8$tAx>QUiGAsJB', '1206692694', 'https://robohash.org/voluptatemfacereeligendi.png?size=50x50&set=set1', 'Assimilated empowering toolset', '1992-10-27', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tallulah Lightollers', 'tlightollers5k', 'tlightollers5k@cbslocal.com', 'gW5>3,/h7O#V2/', '1163880669', 'https://robohash.org/hicquoassumenda.png?size=50x50&set=set1', 'Advanced systemic function', '1977-07-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Drusi Wandrach', 'dwandrach5l', 'dwandrach5l@hibu.com', 'pT1{_=G$>CZXype)', '9051818169', 'https://robohash.org/suntiustoquis.png?size=50x50&set=set1', 'Cross-group empowering collaboration', '2004-04-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Engracia Setterington', 'esetterington5m', 'esetterington5m@discuz.net', 'xO1@>*(djf', '4182134615', 'https://robohash.org/providentquiset.png?size=50x50&set=set1', 'Inverse 6th generation attitude', '1975-01-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bernelle Keble', 'bkeble5n', 'bkeble5n@purevolume.com', 'bJ4.UrNaB},bBrt0', '5595467849', 'https://robohash.org/facereducimusquae.png?size=50x50&set=set1', 'Intuitive leading edge productivity', '1978-01-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Fidole Jopling', 'fjopling5o', 'fjopling5o@howstuffworks.com', 'qX3$,B+7{x(LlNu1', '6197295759', 'https://robohash.org/ipsamtemporibusiste.png?size=50x50&set=set1', 'User-friendly disintermediate artificial intelligence', '1976-01-14', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ashlin Lockwood', 'alockwood5p', 'alockwood5p@opera.com', 'vO4_1OfB!', '4596376606', 'https://robohash.org/illoquasest.png?size=50x50&set=set1', 'Assimilated impactful neural-net', '1975-04-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rik Squires', 'rsquires5q', 'rsquires5q@bizjournals.com', 'dD2''y3qM7i3!', '4168699233', 'https://robohash.org/sednoniure.png?size=50x50&set=set1', 'Reactive optimal core', '2001-03-02', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Emmie Forber', 'eforber5r', 'eforber5r@biblegateway.com', 'iX8%k*yU"', '9109253474', 'https://robohash.org/saepesapientemolestias.png?size=50x50&set=set1', 'Switchable directional Graphical User Interface', '1978-12-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Harris Balazot', 'hbalazot5s', 'hbalazot5s@hc360.com', 'gS6.TMC2', '5905299366', 'https://robohash.org/autdelectuspossimus.png?size=50x50&set=set1', 'Realigned web-enabled circuit', '2000-12-22', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ros Hassey', 'rhassey5t', 'rhassey5t@live.com', 'oU8/bdY$J@Nl*Yul', '5041227433', 'https://robohash.org/etarchitectocumque.png?size=50x50&set=set1', 'Networked holistic conglomeration', '1984-09-03', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Eleanora Peploe', 'epeploe5u', 'epeploe5u@skype.com', 'zY2"tppwL7', '4038251671', 'https://robohash.org/omnisfugareprehenderit.png?size=50x50&set=set1', 'Adaptive local moderator', '1993-04-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Regen Rohlfing', 'rrohlfing5v', 'rrohlfing5v@wufoo.com', 'tN7?H2Z2N|*', '3518224509', 'https://robohash.org/evenietnihilab.png?size=50x50&set=set1', 'Exclusive optimizing Graphical User Interface', '1976-01-21', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jozef Beardshall', 'jbeardshall5w', 'jbeardshall5w@va.gov', 'dW0<IM|veS?Z_7W6', '4761366748', 'https://robohash.org/excepturiquoet.png?size=50x50&set=set1', 'Managed high-level frame', '1977-07-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Stanislaus Millwater', 'smillwater5x', 'smillwater5x@google.co.uk', 'pN2*j)9_F1(mQ', '7063082415', 'https://robohash.org/reiciendisquibusdamiste.png?size=50x50&set=set1', 'Multi-tiered background analyzer', '1993-04-22', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sherilyn Pentecust', 'spentecust5y', 'spentecust5y@php.net', 'dV6/{*nzN', '5524005929', 'https://robohash.org/adipiscicumminima.png?size=50x50&set=set1', 'Inverse next generation portal', '2002-03-26', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Mollee Port', 'mport5z', 'mport5z@stanford.edu', 'iT2%5}Ur.X983', '3373324126', 'https://robohash.org/deseruntsequiiure.png?size=50x50&set=set1', 'Programmable empowering migration', '1998-08-24', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Darryl McSwan', 'dmcswan60', 'dmcswan60@taobao.com', 'uP4*OD*2DuM|m.\', '3629814809', 'https://robohash.org/laboriosamadipisciet.png?size=50x50&set=set1', 'Configurable local groupware', '1998-05-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gerry Yakovliv', 'gyakovliv61', 'gyakovliv61@jalbum.net', 'tA4`j(V<}3c,1,f$', '9201286626', 'https://robohash.org/nonautofficiis.png?size=50x50&set=set1', 'Face to face bandwidth-monitored support', '1976-01-21', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gregorio Waeland', 'gwaeland62', 'gwaeland62@vinaora.com', 'qL2~whUY', '3924235810', 'https://robohash.org/molestiaeevenietplaceat.png?size=50x50&set=set1', 'Cloned 6th generation project', '1989-04-20', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Anselma Skevington', 'askevington63', 'askevington63@timesonline.co.uk', 'oT9|%_LmPl30~m8', '3999282665', 'https://robohash.org/corporislaborumvelit.png?size=50x50&set=set1', 'Programmable zero defect hub', '1976-02-15', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Annalise Paulino', 'apaulino64', 'apaulino64@eventbrite.com', 'tB9"1A1|`yGHr4', '9887140266', 'https://robohash.org/beataefugaomnis.png?size=50x50&set=set1', 'Universal cohesive knowledge user', '2005-01-11', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Roddie Garlick', 'rgarlick65', 'rgarlick65@google.com.hk', 'eS7@!+{C0ZA|>\Na', '5356820507', 'https://robohash.org/aspernaturquised.png?size=50x50&set=set1', 'Triple-buffered 6th generation firmware', '1975-12-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Veriee Frith', 'vfrith66', 'vfrith66@wordpress.org', 'mR3=+}sF9', '4601154150', 'https://robohash.org/dolorquosunt.png?size=50x50&set=set1', 'Cross-group zero administration parallelism', '1991-07-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Roze Mallaby', 'rmallaby67', 'rmallaby67@vkontakte.ru', 'sJ4}nh6+W?', '4502812159', 'https://robohash.org/voluptatesolutasequi.png?size=50x50&set=set1', 'Integrated object-oriented local area network', '1998-02-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Karel O''Shaughnessy', 'koshaughnessy68', 'koshaughnessy68@berkeley.edu', 'bS3%uee`}', '5501049493', 'https://robohash.org/itaquevelin.png?size=50x50&set=set1', 'Ergonomic multi-tasking synergy', '1993-01-02', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Caprice Musker', 'cmusker69', 'cmusker69@blogtalkradio.com', 'bC1@T4~B|H', '4115678612', 'https://robohash.org/eumquameligendi.png?size=50x50&set=set1', 'Team-oriented 24/7 solution', '2001-10-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Giacopo Enefer', 'genefer6a', 'genefer6a@bluehost.com', 'eG4~?#T)p2X,Fp', '3805930365', 'https://robohash.org/undeetlaboriosam.png?size=50x50&set=set1', 'Vision-oriented 6th generation interface', '1978-05-24', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Davidson Crannach', 'dcrannach6b', 'dcrannach6b@gmpg.org', 'pU8,B!oJc7', '7448204328', 'https://robohash.org/doloremqueautmodi.png?size=50x50&set=set1', 'Virtual zero administration support', '2004-08-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Guthry Durban', 'gdurban6c', 'gdurban6c@mysql.com', 'zP2/}*NZz{HjM', '1016143434', 'https://robohash.org/magnamvoluptatemquia.png?size=50x50&set=set1', 'Upgradable content-based collaboration', '2003-09-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Poul Brilon', 'pbrilon6d', 'pbrilon6d@com.com', 'sT5&?oan$aVPlTRV', '9053988992', 'https://robohash.org/nonsapienteminus.png?size=50x50&set=set1', 'Ergonomic neutral methodology', '1988-06-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ximenez Papa', 'xpapa6e', 'xpapa6e@google.com', 'aR1,RD{{A', '1122347917', 'https://robohash.org/utcupiditatelibero.png?size=50x50&set=set1', 'Profound user-facing attitude', '2001-07-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kara Huxtable', 'khuxtable6f', 'khuxtable6f@wikispaces.com', 'cC2\gu%iWu{.?Cx', '8951712447', 'https://robohash.org/eaverodelectus.png?size=50x50&set=set1', 'Persistent bandwidth-monitored hardware', '1991-08-24', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bondon Mangon', 'bmangon6g', 'bmangon6g@dailymail.co.uk', 'xM4<\Q97LvGF/h"', '4606378810', 'https://robohash.org/sapienteevenietsed.png?size=50x50&set=set1', 'Exclusive scalable framework', '1996-09-21', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Zelma Faro', 'zfaro6h', 'zfaro6h@unc.edu', 'yM7>_?%u+i', '5951282117', 'https://robohash.org/eumametquia.png?size=50x50&set=set1', 'Organized neutral data-warehouse', '1971-03-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dru Tournay', 'dtournay6i', 'dtournay6i@wix.com', 'hD5/Kf?LPPikfK1(', '2605229461', 'https://robohash.org/nullaoptionam.png?size=50x50&set=set1', 'Adaptive optimal interface', '2001-09-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Finley Corner', 'fcorner6j', 'fcorner6j@blogger.com', 'sL2\"2"f0H>MhwT', '1444622175', 'https://robohash.org/molestiaseosquia.png?size=50x50&set=set1', 'Ameliorated tertiary orchestration', '1990-02-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Si Godbald', 'sgodbald6k', 'sgodbald6k@google.fr', 'cP2$d/m0S?)`Y+hd', '8529591916', 'https://robohash.org/eosutaccusantium.png?size=50x50&set=set1', 'Team-oriented client-driven internet solution', '2000-10-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Benni Waddell', 'bwaddell6l', 'bwaddell6l@digg.com', 'cI9@uD>7RRY4#>nb', '1078644479', 'https://robohash.org/autetillo.png?size=50x50&set=set1', 'Secured multimedia neural-net', '1984-12-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Vilhelmina Chezelle', 'vchezelle6m', 'vchezelle6m@google.com.au', 'qL8~8''WIi{KA', '6816256503', 'https://robohash.org/rerumquiaautem.png?size=50x50&set=set1', 'Persistent analyzing monitoring', '1973-10-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Humphrey Ondricek', 'hondricek6n', 'hondricek6n@boston.com', 'tU5\}me0k', '8815909664', 'https://robohash.org/autsuntaliquid.png?size=50x50&set=set1', 'Fully-configurable secondary open system', '1976-10-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Carrie Frangione', 'cfrangione6o', 'cfrangione6o@yellowpages.com', 'qH3_l=jB2!T!h', '5842253676', 'https://robohash.org/quaevoluptasveritatis.png?size=50x50&set=set1', 'De-engineered clear-thinking productivity', '1987-11-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sabina Neighbour', 'sneighbour6p', 'sneighbour6p@amazon.co.jp', 'lT2,x),AY', '7365396641', 'https://robohash.org/voluptatibusillototam.png?size=50x50&set=set1', 'Monitored 24 hour superstructure', '1983-07-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Veda Cambling', 'vcambling6q', 'vcambling6q@yolasite.com', 'oE4(+1fA(', '8671894580', 'https://robohash.org/architectodeseruntqui.png?size=50x50&set=set1', 'Integrated encompassing initiative', '1978-11-20', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lenee Bodell', 'lbodell6r', 'lbodell6r@skyrock.com', 'qI5%KSBd"2E', '6999543844', 'https://robohash.org/solutaillumvoluptatem.png?size=50x50&set=set1', 'Synergistic bandwidth-monitored process improvement', '1996-04-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tally Hannah', 'thannah6s', 'thannah6s@google.es', 'rU2(XSh&', '8799158880', 'https://robohash.org/doloresaliquamvelit.png?size=50x50&set=set1', 'Sharable multimedia conglomeration', '1998-06-29', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Darbee Abramson', 'dabramson6t', 'dabramson6t@goo.gl', 'tS9%vpF\Jy}', '2337182442', 'https://robohash.org/estquiapraesentium.png?size=50x50&set=set1', 'Pre-emptive local capability', '1999-03-12', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Elena MacGiffin', 'emacgiffin6u', 'emacgiffin6u@boston.com', 'iO6"XQ}~h', '4131983215', 'https://robohash.org/reiciendisaccusantiumipsa.png?size=50x50&set=set1', 'Balanced didactic installation', '1972-12-15', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Julio Learie', 'jlearie6v', 'jlearie6v@fema.gov', 'vF9{5FN%K5L0w', '3991946468', 'https://robohash.org/blanditiisoccaecatidolores.png?size=50x50&set=set1', 'Persistent transitional project', '1973-02-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Hyacinthe Lambrook', 'hlambrook6w', 'hlambrook6w@dyndns.org', 'zK2)$r4O', '7307824925', 'https://robohash.org/magnamquioptio.png?size=50x50&set=set1', 'Versatile 24/7 collaboration', '1983-01-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Vasili Lagne', 'vlagne6x', 'vlagne6x@arstechnica.com', 'xM7$%=D(m', '8601571824', 'https://robohash.org/voluptatumquiaet.png?size=50x50&set=set1', 'Horizontal empowering implementation', '1975-06-21', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Goldi Melonby', 'gmelonby6y', 'gmelonby6y@msn.com', 'dW7{grIn', '1108915485', 'https://robohash.org/autsolutatempora.png?size=50x50&set=set1', 'Synergistic zero tolerance knowledge base', '1986-02-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Arch Stutard', 'astutard6z', 'astutard6z@army.mil', 'xB3&5+.,YyAyI3~', '5376684466', 'https://robohash.org/autdoloremconsequuntur.png?size=50x50&set=set1', 'Ergonomic homogeneous frame', '1999-04-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cece Ramalho', 'cramalho70', 'cramalho70@dropbox.com', 'sM9{H|qK~8a9?2L', '9952182210', 'https://robohash.org/molestiaenesciuntautem.png?size=50x50&set=set1', 'Innovative modular website', '1976-06-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bert Parnby', 'bparnby71', 'bparnby71@cbsnews.com', 'hG6|nKwt|"#l', '9466911853', 'https://robohash.org/maioresestdoloribus.png?size=50x50&set=set1', 'Virtual coherent support', '1983-04-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Pansie Cashman', 'pcashman72', 'pcashman72@scientificamerican.com', 'rV1`pK*koD', '5599215511', 'https://robohash.org/uteaminus.png?size=50x50&set=set1', 'Intuitive analyzing definition', '2004-02-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Baillie Goodred', 'bgoodred73', 'bgoodred73@digg.com', 'dL1%`qR9Q', '5934698909', 'https://robohash.org/etvoluptasimpedit.png?size=50x50&set=set1', 'Configurable dedicated toolset', '1983-12-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Augustus Tonkin', 'atonkin74', 'atonkin74@ucsd.edu', 'wC6$=%3GNHNCW@ny', '5038889736', 'https://robohash.org/laborumquiasit.png?size=50x50&set=set1', 'User-centric intangible website', '1975-09-27', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Felic Keesman', 'fkeesman75', 'fkeesman75@mysql.com', 'kC1~0sOS%', '7703321623', 'https://robohash.org/velsuntet.png?size=50x50&set=set1', 'Cross-platform bifurcated alliance', '1996-03-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Steve Abthorpe', 'sabthorpe76', 'sabthorpe76@nytimes.com', 'dL6/?+l3k@Izo', '2581187917', 'https://robohash.org/repudiandaedistinctiorerum.png?size=50x50&set=set1', 'Innovative multi-tasking groupware', '1985-03-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Christopher Neathway', 'cneathway77', 'cneathway77@tamu.edu', 'zN7''@>RN8,DC%', '3292298252', 'https://robohash.org/atqueuteum.png?size=50x50&set=set1', 'Customer-focused contextually-based Graphic Interface', '1976-04-20', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Marjorie Stirley', 'mstirley78', 'mstirley78@msu.edu', 'vE7.iG.YURf~JZ8e', '1168355959', 'https://robohash.org/quiasintsint.png?size=50x50&set=set1', 'Mandatory 24/7 project', '1981-05-02', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Roger Raddin', 'rraddin79', 'rraddin79@ted.com', 'uV4.q~hgPQ\>', '1617978799', 'https://robohash.org/liberodeseruntnon.png?size=50x50&set=set1', 'Integrated zero defect projection', '1977-01-08', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sarena Rankine', 'srankine7a', 'srankine7a@netscape.com', 'fV4*zfN($U4a', '3658046296', 'https://robohash.org/voluptasnihilaperiam.png?size=50x50&set=set1', 'Compatible real-time infrastructure', '1994-09-27', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Salvidor Chawner', 'schawner7b', 'schawner7b@zimbio.com', 'yR4`m''0Z%QLv}', '9113995926', 'https://robohash.org/aperiamsuntconsectetur.png?size=50x50&set=set1', 'Ameliorated hybrid adapter', '1990-10-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Max Dance', 'mdance7c', 'mdance7c@gmpg.org', 'sO3?PpK#+qoM&', '3715758247', 'https://robohash.org/enimaccusantiumet.png?size=50x50&set=set1', 'Vision-oriented object-oriented software', '1980-07-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Loralee Tomasik', 'ltomasik7d', 'ltomasik7d@1688.com', 'xI6{SgYb`QR', '2341492943', 'https://robohash.org/etquibusdamsunt.png?size=50x50&set=set1', 'Exclusive reciprocal data-warehouse', '1994-02-28', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Logan Rizzone', 'lrizzone7e', 'lrizzone7e@gnu.org', 'wM7.WGEd$y*SZ9//', '5906766224', 'https://robohash.org/aperiamdelenitised.png?size=50x50&set=set1', 'Reactive disintermediate archive', '1980-01-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Evangelina Bowie', 'ebowie7f', 'ebowie7f@baidu.com', 'dX3,Q_A/gS`', '3152707911', 'https://robohash.org/estarchitectoet.png?size=50x50&set=set1', 'Compatible needs-based system engine', '1980-04-02', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Florri O''Criane', 'focriane7g', 'focriane7g@yahoo.co.jp', 'rP2+Se>e', '2801704600', 'https://robohash.org/esserepudiandaeet.png?size=50x50&set=set1', 'Cross-group well-modulated architecture', '1992-12-22', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Stinky Leathart', 'sleathart7h', 'sleathart7h@economist.com', 'oA9?WTkt%eOeZ?xQ', '6966258741', 'https://robohash.org/eteosqui.png?size=50x50&set=set1', 'Customer-focused client-server forecast', '1991-01-31', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Erna Whapples', 'ewhapples7i', 'ewhapples7i@360.cn', 'jK7&glT<P/./Uf', '2696547530', 'https://robohash.org/ipsumquoslabore.png?size=50x50&set=set1', 'Visionary leading edge matrix', '1989-05-17', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lishe Geerling', 'lgeerling7j', 'lgeerling7j@nymag.com', 'tN0+tXYr1X{RoZ', '8582522946', 'https://robohash.org/quasidebitisnihil.png?size=50x50&set=set1', 'Managed zero tolerance matrix', '1986-11-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ardith Dowdle', 'adowdle7k', 'adowdle7k@e-recht24.de', 'xY7*y+|`T+..T', '2547390904', 'https://robohash.org/suntcommodienim.png?size=50x50&set=set1', 'User-friendly intermediate concept', '2000-10-14', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cybill Fluger', 'cfluger7l', 'cfluger7l@nsw.gov.au', 'oK6.Ez.47ZJ', '4463163902', 'https://robohash.org/estetnobis.png?size=50x50&set=set1', 'Progressive clear-thinking policy', '1989-09-25', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ernst Colgrave', 'ecolgrave7m', 'ecolgrave7m@ibm.com', 'wJ8@w=27`(TdzH', '6718916914', 'https://robohash.org/repellatquialaborum.png?size=50x50&set=set1', 'Persistent 3rd generation pricing structure', '2002-04-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Willa Scimoni', 'wscimoni7n', 'wscimoni7n@discuz.net', 'gX1$VI&lmfVrHrz', '6261927188', 'https://robohash.org/veletpraesentium.png?size=50x50&set=set1', 'Secured contextually-based architecture', '1984-07-29', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Franny Sailor', 'fsailor7o', 'fsailor7o@yelp.com', 'dW0(''nv''2\`9K9', '9672327951', 'https://robohash.org/similiquedolorin.png?size=50x50&set=set1', 'Decentralized clear-thinking frame', '1983-12-10', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dorena Duigenan', 'dduigenan7p', 'dduigenan7p@marketwatch.com', 'oY5.axZxH/e', '6187442577', 'https://robohash.org/nostrumquameveniet.png?size=50x50&set=set1', 'Horizontal client-driven framework', '1997-05-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Glenna Giroldo', 'ggiroldo7q', 'ggiroldo7q@storify.com', 'kD2?h(Zv($_Z"', '6067801310', 'https://robohash.org/voluptatumrationeet.png?size=50x50&set=set1', 'Digitized non-volatile encryption', '1989-08-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Brooks Lindup', 'blindup7r', 'blindup7r@miibeian.gov.cn', 'zP1\{4V|JNJtl*Vq', '5638045988', 'https://robohash.org/dolorcumquedolorum.png?size=50x50&set=set1', 'Right-sized next generation function', '1983-03-24', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sherline Mewha', 'smewha7s', 'smewha7s@friendfeed.com', 'kI9,fWz\{wA#"\', '3718703657', 'https://robohash.org/praesentiumquasimolestias.png?size=50x50&set=set1', 'Horizontal intermediate open system', '1989-08-12', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jenica Bonwell', 'jbonwell7t', 'jbonwell7t@patch.com', 'cG0{rUA9ca)J#P', '8246231991', 'https://robohash.org/quameumquia.png?size=50x50&set=set1', 'Triple-buffered bifurcated instruction set', '1990-09-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Annelise Woolaston', 'awoolaston7u', 'awoolaston7u@php.net', 'gM2,#0jbKlwmr', '9643804741', 'https://robohash.org/etdoloribusminima.png?size=50x50&set=set1', 'Secured interactive installation', '1979-01-26', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Keefer Lambrook', 'klambrook7v', 'klambrook7v@cnn.com', 'mR0?Bp&xkHe', '1585105559', 'https://robohash.org/rerumipsumneque.png?size=50x50&set=set1', 'Visionary asymmetric array', '2003-10-12', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Serena Power', 'spower7w', 'spower7w@wix.com', 'jT4_p16s&20#*&v', '3122214521', 'https://robohash.org/cumquiqui.png?size=50x50&set=set1', 'Vision-oriented explicit knowledge user', '1982-04-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kenyon Fautley', 'kfautley7x', 'kfautley7x@arizona.edu', 'rD3~4yhfCgSlBE', '8024879513', 'https://robohash.org/inventorerepudiandaeconsequatur.png?size=50x50&set=set1', 'Mandatory secondary collaboration', '1998-11-15', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tony Ruscoe', 'truscoe7y', 'truscoe7y@ucsd.edu', 'wF4>v_f~/laaX', '8451719001', 'https://robohash.org/magnamnonqui.png?size=50x50&set=set1', 'Open-architected non-volatile groupware', '1993-09-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gino Werny', 'gwerny7z', 'gwerny7z@qq.com', 'fN8!8+8P', '6513713932', 'https://robohash.org/dolorpossimusiusto.png?size=50x50&set=set1', 'Implemented bifurcated analyzer', '1980-04-21', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Nalani Sidon', 'nsidon80', 'nsidon80@ocn.ne.jp', 'oM6.1.plq', '4907037692', 'https://robohash.org/essefugiatcupiditate.png?size=50x50&set=set1', 'Assimilated multi-state array', '1996-04-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Wright Pock', 'wpock81', 'wpock81@google.com.hk', 'iC8?a=hBSkc4', '3975109752', 'https://robohash.org/utexvoluptatem.png?size=50x50&set=set1', 'Triple-buffered actuating emulation', '1971-07-17', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Di Geroldi', 'dgeroldi82', 'dgeroldi82@google.ca', 'xZ9+DH8a', '4748700437', 'https://robohash.org/auteiusea.png?size=50x50&set=set1', 'Open-architected dynamic protocol', '1992-10-22', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gard Pomfret', 'gpomfret83', 'gpomfret83@columbia.edu', 'xE6(\d+18%x,', '7899949488', 'https://robohash.org/evenietaspernaturdicta.png?size=50x50&set=set1', 'Enhanced human-resource info-mediaries', '2003-08-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Syd Rewbottom', 'srewbottom84', 'srewbottom84@cam.ac.uk', 'lY1$L17~dp)WQzLJ', '3361055536', 'https://robohash.org/quoquasipraesentium.png?size=50x50&set=set1', 'Optional bandwidth-monitored framework', '1979-04-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kellia Izachik', 'kizachik85', 'kizachik85@chronoengine.com', 'qO1~!AkX''', '4817198940', 'https://robohash.org/doloremculpaet.png?size=50x50&set=set1', 'User-friendly homogeneous analyzer', '1986-05-31', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Stavro Kendrick', 'skendrick86', 'skendrick86@canalblog.com', 'sD4)RS1+', '4321516799', 'https://robohash.org/perspiciatisnullaenim.png?size=50x50&set=set1', 'Realigned radical focus group', '1999-10-22', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Risa Kenrick', 'rkenrick87', 'rkenrick87@accuweather.com', 'nA6~WFs_02z', '9181218704', 'https://robohash.org/quidemasperioressoluta.png?size=50x50&set=set1', 'Integrated zero tolerance intranet', '1999-10-28', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Reba Mordaunt', 'rmordaunt88', 'rmordaunt88@uol.com.br', 'cK1)*I,)Z8=>RRs', '5541231815', 'https://robohash.org/adblanditiisest.png?size=50x50&set=set1', 'Future-proofed even-keeled monitoring', '1975-04-20', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Arlen Griffitts', 'agriffitts89', 'agriffitts89@delicious.com', 'cD0&i1BrT#|', '5609851976', 'https://robohash.org/voluptasestaliquid.png?size=50x50&set=set1', 'De-engineered client-server analyzer', '1971-12-14', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Almeda Hambright', 'ahambright8a', 'ahambright8a@forbes.com', 'tK6=rgTN>', '5905018040', 'https://robohash.org/eafugitexcepturi.png?size=50x50&set=set1', 'Down-sized static middleware', '1993-03-26', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Norbert Barkhouse', 'nbarkhouse8b', 'nbarkhouse8b@topsy.com', 'kG0{2zx=|', '8501897982', 'https://robohash.org/doloretnon.png?size=50x50&set=set1', 'Profound 3rd generation hub', '2004-10-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Marquita Bamber', 'mbamber8c', 'mbamber8c@globo.com', 'jR5$5%sJV`{vd', '5361951899', 'https://robohash.org/laudantiumrerumautem.png?size=50x50&set=set1', 'Front-line zero defect function', '2000-02-10', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Luciano Childers', 'lchilders8d', 'lchilders8d@hhs.gov', 'jG6)gZh*j},', '6434064544', 'https://robohash.org/voluptasillosit.png?size=50x50&set=set1', 'Open-architected content-based knowledge base', '2000-02-25', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Zak Doby', 'zdoby8e', 'zdoby8e@addthis.com', 'qX4*X}?IzWGD', '4089202987', 'https://robohash.org/etdolorumcum.png?size=50x50&set=set1', 'Grass-roots bandwidth-monitored moderator', '1976-11-10', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Westley Fredy', 'wfredy8f', 'wfredy8f@comcast.net', 'zS8*W5V{@J0}!3.', '7668882247', 'https://robohash.org/laboriosamconsequunturatque.png?size=50x50&set=set1', 'Right-sized non-volatile info-mediaries', '2005-09-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gertruda Skynner', 'gskynner8g', 'gskynner8g@addthis.com', 'yA6/|NJxVbY>', '2027067916', 'https://robohash.org/eaquenonut.png?size=50x50&set=set1', 'Open-architected didactic capability', '1975-05-29', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kurt Chrystal', 'kchrystal8h', 'kchrystal8h@de.vu', 'rG5%zny$ixW&zOr/', '6022523760', 'https://robohash.org/excepturirepellendussequi.png?size=50x50&set=set1', 'Polarised radical solution', '1994-10-31', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Britni Matas', 'bmatas8i', 'bmatas8i@reference.com', 'bI8"u\f!f', '4499951624', 'https://robohash.org/etmaximelaboriosam.png?size=50x50&set=set1', 'Switchable analyzing open architecture', '1977-09-07', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ode Ghent', 'oghent8j', 'oghent8j@ow.ly', 'eY4!%61PCv\Xo5', '1231867284', 'https://robohash.org/corporisexpeditaa.png?size=50x50&set=set1', 'Implemented national ability', '1990-06-05', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Franklyn McFie', 'fmcfie8k', 'fmcfie8k@eventbrite.com', 'gH9,"vczPnPGYJI', '8903771212', 'https://robohash.org/assumendaautodio.png?size=50x50&set=set1', 'Assimilated tertiary instruction set', '1974-01-15', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bird Feares', 'bfeares8l', 'bfeares8l@sbwire.com', 'qK2/`FmxxC8JM..', '4942418684', 'https://robohash.org/idquifacilis.png?size=50x50&set=set1', 'Distributed demand-driven neural-net', '1988-04-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Drucie Whitnell', 'dwhitnell8m', 'dwhitnell8m@ted.com', 'qF1}q6Dgo2QGWk', '3028447138', 'https://robohash.org/inventoredolorillum.png?size=50x50&set=set1', 'Synchronised non-volatile ability', '2003-07-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Valentin Bernat', 'vbernat8n', 'vbernat8n@tripod.com', 'bY3>RlNi"@', '2819876433', 'https://robohash.org/doloremvelitodit.png?size=50x50&set=set1', 'Assimilated methodical capability', '1971-08-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jordanna Cristofolini', 'jcristofolini8o', 'jcristofolini8o@mail.ru', 'oQ5!DTqv%xd', '1529499494', 'https://robohash.org/repudiandaequolaborum.png?size=50x50&set=set1', 'Streamlined multi-state matrix', '1987-05-08', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rodina Bartolommeo', 'rbartolommeo8p', 'rbartolommeo8p@t-online.de', 'qJ2|iAr&eHLUzRj\', '1155738393', 'https://robohash.org/quiadoloremanimi.png?size=50x50&set=set1', 'Reduced directional matrix', '1973-03-22', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dorry Mainz', 'dmainz8q', 'dmainz8q@lycos.com', 'yC7>Pp"C=Ba>n0N', '3072754311', 'https://robohash.org/quaerepellatat.png?size=50x50&set=set1', 'Re-contextualized foreground paradigm', '1973-02-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Brittne Gillcrist', 'bgillcrist8r', 'bgillcrist8r@oaic.gov.au', 'bC7/=YLIIV/(2r', '2006270623', 'https://robohash.org/omnisvitaeducimus.png?size=50x50&set=set1', 'Robust needs-based paradigm', '2001-07-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sophi Habbershon', 'shabbershon8s', 'shabbershon8s@google.com.hk', 'aS5$HwdmwR>JFA=F', '5029425797', 'https://robohash.org/quoquiaest.png?size=50x50&set=set1', 'Multi-channelled 4th generation open architecture', '2001-04-17', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jeana Maxweell', 'jmaxweell8t', 'jmaxweell8t@springer.com', 'yN0_b#6dU7', '7644039527', 'https://robohash.org/earumofficiasoluta.png?size=50x50&set=set1', 'Managed neutral matrices', '1996-12-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Annabelle Tompkin', 'atompkin8u', 'atompkin8u@t.co', 'bH4{}HH%/nv~|>', '4105895208', 'https://robohash.org/voluptatumipsumea.png?size=50x50&set=set1', 'Reduced human-resource implementation', '1999-02-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Orville Seakings', 'oseakings8v', 'oseakings8v@ox.ac.uk', 'xJ1(!)VZa4og', '4809685154', 'https://robohash.org/eumaliasvoluptatem.png?size=50x50&set=set1', 'Visionary disintermediate hardware', '1991-01-15', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ronna Basek', 'rbasek8w', 'rbasek8w@artisteer.com', 'bF1''S~fB?r', '5206830536', 'https://robohash.org/voluptasdoloremat.png?size=50x50&set=set1', 'Managed homogeneous ability', '2000-12-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Hercules Fidelus', 'hfidelus8x', 'hfidelus8x@domainmarket.com', 'eX3@n!>!jg.\', '6093431022', 'https://robohash.org/culpaomnismodi.png?size=50x50&set=set1', 'Optional actuating database', '2000-04-21', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Junette Fowlds', 'jfowlds8y', 'jfowlds8y@fastcompany.com', 'kO3<wo&c"\\9oLV', '1213113101', 'https://robohash.org/voluptasquiaaliquid.png?size=50x50&set=set1', 'Monitored composite conglomeration', '1998-09-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dav Thomlinson', 'dthomlinson8z', 'dthomlinson8z@ucoz.ru', 'bG8)xp6&>D7OH1', '2181631042', 'https://robohash.org/ipsarationequasi.png?size=50x50&set=set1', 'Innovative well-modulated attitude', '1971-07-14', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Billy Rembaud', 'brembaud90', 'brembaud90@unicef.org', 'jK2/)uLVE5l', '4702465084', 'https://robohash.org/quamvelnemo.png?size=50x50&set=set1', 'Proactive incremental pricing structure', '1974-08-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lonnie Stapele', 'lstapele91', 'lstapele91@blinklist.com', 'iB0!uXNq5}@OVc', '9139314837', 'https://robohash.org/autadeos.png?size=50x50&set=set1', 'De-engineered leading edge internet solution', '1995-08-28', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Theo Marten', 'tmarten92', 'tmarten92@dagondesign.com', 'rJ9$sxbpzR{KI', '5321410566', 'https://robohash.org/eaquealiquamut.png?size=50x50&set=set1', 'User-centric foreground middleware', '1972-04-28', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Clive Barlass', 'cbarlass93', 'cbarlass93@prlog.org', 'oR3+5@.t!l', '2501342348', 'https://robohash.org/evenietnumquamtotam.png?size=50x50&set=set1', 'Expanded 24 hour toolset', '1995-08-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bordie Duffett', 'bduffett94', 'bduffett94@chicagotribune.com', 'tN4_NF9oD5ztZ5', '9936965996', 'https://robohash.org/earumperspiciatisdolorem.png?size=50x50&set=set1', 'Optional value-added Graphic Interface', '1994-07-31', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tandy Treadgear', 'ttreadgear95', 'ttreadgear95@engadget.com', 'rF0=1GP_S1@,', '2263053915', 'https://robohash.org/consequaturetet.png?size=50x50&set=set1', 'Pre-emptive 6th generation architecture', '1992-05-14', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Daisie Whitloe', 'dwhitloe96', 'dwhitloe96@icio.us', 'yI1~fXQ.,l,{"', '4511518259', 'https://robohash.org/estvoluptatemaut.png?size=50x50&set=set1', 'Enterprise-wide intermediate hardware', '1983-03-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Pia Milham', 'pmilham97', 'pmilham97@webeden.co.uk', 'qS3!+e"QmRWZ1', '8873377067', 'https://robohash.org/quiatotamdelectus.png?size=50x50&set=set1', 'Team-oriented cohesive knowledge base', '1982-09-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Glynnis Douce', 'gdouce98', 'gdouce98@spotify.com', 'iI8+r6YS', '5813292793', 'https://robohash.org/necessitatibusametpossimus.png?size=50x50&set=set1', 'Cross-group empowering firmware', '1989-01-24', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Major Grichukhanov', 'mgrichukhanov99', 'mgrichukhanov99@un.org', 'sT9!EQ4(FPapi@?O', '2196834473', 'https://robohash.org/velilloeius.png?size=50x50&set=set1', 'Distributed zero tolerance hub', '2000-04-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Beitris Minget', 'bminget9a', 'bminget9a@yandex.ru', 'jC2?HRBP"CN', '5696764850', 'https://robohash.org/nemomagnamest.png?size=50x50&set=set1', 'Vision-oriented object-oriented algorithm', '1971-10-17', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Marjory Febvre', 'mfebvre9b', 'mfebvre9b@ftc.gov', 'jW8`jw&.&cvxZy', '9307899694', 'https://robohash.org/suscipitautoccaecati.png?size=50x50&set=set1', 'Persistent web-enabled task-force', '1990-01-03', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Michelina Sidgwick', 'msidgwick9c', 'msidgwick9c@smugmug.com', 'dH5}E@XhJcI', '5174214785', 'https://robohash.org/undeseddolorem.png?size=50x50&set=set1', 'Pre-emptive human-resource matrices', '1988-02-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ulrick O''Hickey', 'uohickey9d', 'uohickey9d@nymag.com', 'dG2.oV!|T.uzj', '1759727428', 'https://robohash.org/sapientetemporibusnecessitatibus.png?size=50x50&set=set1', 'Mandatory leading edge conglomeration', '1982-05-17', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Alethea Mushart', 'amushart9e', 'amushart9e@nsw.gov.au', 'dN0_x\,BCk#*', '9697794387', 'https://robohash.org/voluptatemametsint.png?size=50x50&set=set1', 'Triple-buffered stable open architecture', '1997-10-16', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Fredra Burchmore', 'fburchmore9f', 'fburchmore9f@over-blog.com', 'vD9>&6TdfLcwPD', '8257546244', 'https://robohash.org/asperioresassumendarecusandae.png?size=50x50&set=set1', 'Upgradable impactful concept', '1977-04-20', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sawyere Rixon', 'srixon9g', 'srixon9g@skype.com', 'rR5$nIAj', '2108486597', 'https://robohash.org/nemoundead.png?size=50x50&set=set1', 'Distributed national attitude', '1992-07-21', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ansel Jellico', 'ajellico9h', 'ajellico9h@japanpost.jp', 'pY5_{cn=Im"S', '2491610850', 'https://robohash.org/doloresexsed.png?size=50x50&set=set1', 'Total coherent infrastructure', '1995-09-05', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gina Bailiss', 'gbailiss9i', 'gbailiss9i@washingtonpost.com', 'sW0+cB$b''3&>', '7105272934', 'https://robohash.org/suntquidemporro.png?size=50x50&set=set1', 'Down-sized methodical portal', '1973-08-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Eugenie Lockart', 'elockart9j', 'elockart9j@vk.com', 'rX0"c*5U%3wE', '5404666869', 'https://robohash.org/suntestsoluta.png?size=50x50&set=set1', 'Open-source solution-oriented time-frame', '1997-01-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Holly Maw', 'hmaw9k', 'hmaw9k@go.com', 'aP2`pgR4$@', '3996457117', 'https://robohash.org/abnihilvoluptas.png?size=50x50&set=set1', 'Focused multi-tasking firmware', '1974-01-28', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jeth Base', 'jbase9l', 'jbase9l@un.org', 'hI8!?''k6L', '6467179116', 'https://robohash.org/inrerumquidem.png?size=50x50&set=set1', 'Open-source foreground groupware', '1994-03-11', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Maia Ibell', 'mibell9m', 'mibell9m@sohu.com', 'yZ0.wr}Y%Q=QMi$', '6754879847', 'https://robohash.org/numquamomnisducimus.png?size=50x50&set=set1', 'Re-contextualized fresh-thinking focus group', '1991-10-08', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jeralee Alpin', 'jalpin9n', 'jalpin9n@histats.com', 'kH6<yT0@46', '5807609859', 'https://robohash.org/etporronatus.png?size=50x50&set=set1', 'Function-based well-modulated functionalities', '1979-05-19', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ninnetta Merner', 'nmerner9o', 'nmerner9o@exblog.jp', 'fN3{kpiwI<`c#twP', '7687069766', 'https://robohash.org/optiotemporibusquas.png?size=50x50&set=set1', 'Virtual needs-based focus group', '1972-07-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jonathon Mussen', 'jmussen9p', 'jmussen9p@wordpress.com', 'wB3.kOQYCui?H+z', '7234005626', 'https://robohash.org/inventoreadsit.png?size=50x50&set=set1', 'Innovative grid-enabled flexibility', '1976-06-08', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cole Vaggers', 'cvaggers9q', 'cvaggers9q@ft.com', 'zJ7$)3,R1', '9261811441', 'https://robohash.org/possimusipsaomnis.png?size=50x50&set=set1', 'Multi-tiered systematic project', '1996-03-19', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Helaine Kovalski', 'hkovalski9r', 'hkovalski9r@a8.net', 'pD0/y|n!_', '2383258272', 'https://robohash.org/utaccusamusut.png?size=50x50&set=set1', 'Networked systemic hardware', '1989-05-14', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Saidee Matuszkiewicz', 'smatuszkiewicz9s', 'smatuszkiewicz9s@tamu.edu', 'gM8=5ylpW1w=q', '3287567555', 'https://robohash.org/quinostrumducimus.png?size=50x50&set=set1', 'Expanded context-sensitive artificial intelligence', '1970-12-21', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Savina Jesson', 'sjesson9t', 'sjesson9t@ning.com', 'xE9(gpKdBy>*jW', '8348092017', 'https://robohash.org/quieaqueest.png?size=50x50&set=set1', 'Exclusive context-sensitive algorithm', '1997-09-29', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Laurette Gentil', 'lgentil9u', 'lgentil9u@wsj.com', 'sQ7@!''7`Q3', '1573962372', 'https://robohash.org/voluptatumetex.png?size=50x50&set=set1', 'Profound contextually-based parallelism', '2004-07-08', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Almeta Yurshev', 'ayurshev9v', 'ayurshev9v@vk.com', 'aL8&OdocfBBv', '4864904487', 'https://robohash.org/iureveritatisconsequuntur.png?size=50x50&set=set1', 'Networked multimedia solution', '1985-05-14', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sabrina Karpe', 'skarpe9w', 'skarpe9w@over-blog.com', 'kL2@9#(c)_(k>i', '8166342069', 'https://robohash.org/aliquidilloplaceat.png?size=50x50&set=set1', 'Expanded zero defect adapter', '1996-11-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Glynis Clow', 'gclow9x', 'gclow9x@nature.com', 'uH0*9xrMgnK$pa7', '6864501566', 'https://robohash.org/sitquiaquo.png?size=50x50&set=set1', 'Monitored systemic hierarchy', '1990-09-27', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Doralin Smalles', 'dsmalles9y', 'dsmalles9y@ycombinator.com', 'gY1_k{WC/', '6548952006', 'https://robohash.org/magnamutamet.png?size=50x50&set=set1', 'Diverse mission-critical standardization', '1992-07-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kenn Revens', 'krevens9z', 'krevens9z@issuu.com', 'sJ1_nChcmeu', '4934293042', 'https://robohash.org/quiasaepesimilique.png?size=50x50&set=set1', 'Proactive fault-tolerant intranet', '1993-04-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Danella Rayworth', 'draywortha0', 'draywortha0@issuu.com', 'zI9.zWM<,2Pz', '5854353196', 'https://robohash.org/rationeestfugit.png?size=50x50&set=set1', 'Cross-platform static secured line', '1994-01-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Nikolia Mc Gaughey', 'nmca1', 'nmca1@mtv.com', 'sT0<GR}ct&~?(6ft', '4059633675', 'https://robohash.org/nostrumvoluptateslaborum.png?size=50x50&set=set1', 'Optional high-level contingency', '1985-12-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Maurie Getcliff', 'mgetcliffa2', 'mgetcliffa2@cdbaby.com', 'pI4`hi!hK', '6118111574', 'https://robohash.org/doloribusetvoluptate.png?size=50x50&set=set1', 'Re-contextualized logistical customer loyalty', '1998-01-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kareem McMurty', 'kmcmurtya3', 'kmcmurtya3@slideshare.net', 'oG7=K<T}EX', '8122193968', 'https://robohash.org/nesciuntadsed.png?size=50x50&set=set1', 'Total high-level flexibility', '1972-09-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Farra Rooze', 'froozea4', 'froozea4@google.it', 'uS4,C3BVHH', '8034621274', 'https://robohash.org/officiarerumqui.png?size=50x50&set=set1', 'Polarised asynchronous throughput', '1979-04-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Michell Richmond', 'mrichmonda5', 'mrichmonda5@shinystat.com', 'pZ9(m>Zxiln|,lXb', '9871467411', 'https://robohash.org/maximerationedoloribus.png?size=50x50&set=set1', 'Re-engineered incremental middleware', '2001-11-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Thaxter MacKnight', 'tmacknighta6', 'tmacknighta6@typepad.com', 'eF0,x=''K6i', '8687152446', 'https://robohash.org/commodietut.png?size=50x50&set=set1', 'Diverse intermediate alliance', '1984-11-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lyndell Brayfield', 'lbrayfielda7', 'lbrayfielda7@ed.gov', 'rB5&g&v0LvD&', '3807439121', 'https://robohash.org/oditeatemporibus.png?size=50x50&set=set1', 'Universal radical time-frame', '1974-07-26', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Elianore Blanc', 'eblanca8', 'eblanca8@wunderground.com', 'hU1#M+t=`NKL6''$J', '5039565296', 'https://robohash.org/facerevoluptatemet.png?size=50x50&set=set1', 'Open-architected cohesive customer loyalty', '1993-05-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Emile Stebbings', 'estebbingsa9', 'estebbingsa9@alibaba.com', 'yY6_Z|TMl9d', '9617061568', 'https://robohash.org/indelectustotam.png?size=50x50&set=set1', 'Realigned dedicated data-warehouse', '1973-10-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lars Noel', 'lnoelaa', 'lnoelaa@hp.com', 'jN7~Ok*?<qEc', '4281261480', 'https://robohash.org/nesciuntearumdolorem.png?size=50x50&set=set1', 'Intuitive high-level leverage', '1981-10-17', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Judah Sivill', 'jsivillab', 'jsivillab@1und1.de', 'uM3$v2Y~48wO"%&', '2861185229', 'https://robohash.org/suntundequis.png?size=50x50&set=set1', 'Public-key contextually-based groupware', '2001-06-12', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jaine Knell', 'jknellac', 'jknellac@cdc.gov', 'uX7@F}''@NR''O$r', '8303897254', 'https://robohash.org/eosassumendanesciunt.png?size=50x50&set=set1', 'Versatile regional neural-net', '1984-01-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cathlene Sunnucks', 'csunnucksad', 'csunnucksad@seesaa.net', 'gJ0=L9!Wly', '8629279971', 'https://robohash.org/molestiasliberoquo.png?size=50x50&set=set1', 'Profit-focused systematic knowledge user', '1987-11-12', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Shell Corbet', 'scorbetae', 'scorbetae@toplist.cz', 'fZ7}s&53', '4563723267', 'https://robohash.org/magniveroad.png?size=50x50&set=set1', 'Exclusive background firmware', '1994-12-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sophi Curling', 'scurlingaf', 'scurlingaf@dell.com', 'nJ3%63n6', '8827868456', 'https://robohash.org/quasiblanditiisea.png?size=50x50&set=set1', 'Multi-channelled motivating methodology', '1976-01-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Brinna Borrott', 'bborrottag', 'bborrottag@alibaba.com', 'tA5|OHZN3_1+4.KG', '8252069283', 'https://robohash.org/numquamautemsit.png?size=50x50&set=set1', 'Cloned didactic secured line', '1997-08-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Conroy Marlon', 'cmarlonah', 'cmarlonah@nps.gov', 'zV9,''S+fggz', '7875999077', 'https://robohash.org/autemeadolorem.png?size=50x50&set=set1', 'Pre-emptive stable pricing structure', '1977-04-22', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gabie Shenley', 'gshenleyai', 'gshenleyai@thetimes.co.uk', 'uI1}lF6iT(jlb', '4971594828', 'https://robohash.org/adinciduntcupiditate.png?size=50x50&set=set1', 'Networked systemic task-force', '1984-01-14', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Faustine Sjostrom', 'fsjostromaj', 'fsjostromaj@devhub.com', 'mU1)6=If', '9361673984', 'https://robohash.org/occaecatietanimi.png?size=50x50&set=set1', 'Progressive solution-oriented Graphic Interface', '1992-09-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Vita Lincke', 'vlinckeak', 'vlinckeak@netscape.com', 'xO9(/9x7', '9101929139', 'https://robohash.org/etquocumque.png?size=50x50&set=set1', 'Extended regional software', '1971-07-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Carole Joret', 'cjoretal', 'cjoretal@ifeng.com', 'sP8/8qX8E', '6739120109', 'https://robohash.org/maioresestconsectetur.png?size=50x50&set=set1', 'Reactive logistical software', '1977-05-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Georgianne Pincked', 'gpinckedam', 'gpinckedam@studiopress.com', 'hO1%`ooX7CP~', '4322680929', 'https://robohash.org/quaeculpaerror.png?size=50x50&set=set1', 'Balanced intangible focus group', '1977-03-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Minnaminnie Smolan', 'msmolanan', 'msmolanan@wufoo.com', 'fO9>V@fBss', '4415531876', 'https://robohash.org/autexpeditaerror.png?size=50x50&set=set1', 'Monitored multimedia installation', '1976-06-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Aurlie Loggie', 'aloggieao', 'aloggieao@weebly.com', 'uE0,MijJ~Y@', '7258336841', 'https://robohash.org/ipsameaoccaecati.png?size=50x50&set=set1', 'Innovative interactive installation', '1983-02-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gallard Chater', 'gchaterap', 'gchaterap@weibo.com', 'jK5)6+DU%', '3267105100', 'https://robohash.org/providentexnon.png?size=50x50&set=set1', 'Business-focused intangible flexibility', '1991-01-08', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lina Gai', 'lgaiaq', 'lgaiaq@sakura.ne.jp', 'vU4@*||E{T~0BD9', '7207906480', 'https://robohash.org/quasfugiatporro.png?size=50x50&set=set1', 'Balanced 3rd generation support', '1981-07-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Didi Peert', 'dpeertar', 'dpeertar@imageshack.us', 'iP8?>t!|Ic!SELr', '4858969089', 'https://robohash.org/eaodioveniam.png?size=50x50&set=set1', 'Streamlined eco-centric knowledge base', '1988-03-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lexy Verralls', 'lverrallsas', 'lverrallsas@cafepress.com', 'zF4''ssP*"gE&2=n', '4952711307', 'https://robohash.org/enimvelitvoluptatem.png?size=50x50&set=set1', 'Ameliorated exuding policy', '1989-08-09', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Isacco O''Hannigan', 'iohanniganat', 'iohanniganat@cnbc.com', 'hR9''K?dA*?isW', '9023795530', 'https://robohash.org/idexcepturivoluptatum.png?size=50x50&set=set1', 'Implemented grid-enabled infrastructure', '1997-12-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ade Andell', 'aandellau', 'aandellau@uol.com.br', 'qC1/8pveNSiK', '7415941883', 'https://robohash.org/harumconsequaturexpedita.png?size=50x50&set=set1', 'Enterprise-wide discrete migration', '2003-08-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Huntington Gawkes', 'hgawkesav', 'hgawkesav@last.fm', 'gF0`j3~/%N', '2999800248', 'https://robohash.org/undenisidignissimos.png?size=50x50&set=set1', 'Innovative client-driven superstructure', '1971-06-23', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Joela Claypole', 'jclaypoleaw', 'jclaypoleaw@hostgator.com', 'zA9<7SggG', '5166287037', 'https://robohash.org/namlaboredelectus.png?size=50x50&set=set1', 'Front-line 5th generation alliance', '1971-06-17', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Phylys Croke', 'pcrokeax', 'pcrokeax@odnoklassniki.ru', 'aZ7''*eBAhI._>,c5', '6104434445', 'https://robohash.org/quiserrorconsequatur.png?size=50x50&set=set1', 'Persistent directional benchmark', '1981-12-07', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Doyle Nafzger', 'dnafzgeray', 'dnafzgeray@mapy.cz', 'wE8+.Wq@hk|$', '9073643668', 'https://robohash.org/eaexplicabounde.png?size=50x50&set=set1', 'Organic modular process improvement', '1991-12-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Annamarie Sheriff', 'asheriffaz', 'asheriffaz@fotki.com', 'uE9|Q){4', '5665822570', 'https://robohash.org/autvoluptasqui.png?size=50x50&set=set1', 'Mandatory eco-centric forecast', '1979-12-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rourke Tookey', 'rtookeyb0', 'rtookeyb0@walmart.com', 'wP0\ug6MM.', '4107925280', 'https://robohash.org/estarchitectounde.png?size=50x50&set=set1', 'Re-contextualized bifurcated framework', '1978-11-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ban Crepin', 'bcrepinb1', 'bcrepinb1@goodreads.com', 'nC0%oG<+L', '5447484172', 'https://robohash.org/omnisinmaiores.png?size=50x50&set=set1', 'Monitored secondary attitude', '1981-06-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Virgie Janosevic', 'vjanosevicb2', 'vjanosevicb2@irs.gov', 'yF6(9(4jA8d', '1359427687', 'https://robohash.org/perspiciatisatquequi.png?size=50x50&set=set1', 'Future-proofed analyzing database', '1992-04-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Murray Lambotin', 'mlambotinb3', 'mlambotinb3@japanpost.jp', 'tP3`}=|ueb', '3409397956', 'https://robohash.org/corporispariaturid.png?size=50x50&set=set1', 'Total systematic monitoring', '1977-06-02', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kevin McReynold', 'kmcreynoldb4', 'kmcreynoldb4@scientificamerican.com', 'uG5.sK\B*', '5184602177', 'https://robohash.org/eamollitiaprovident.png?size=50x50&set=set1', 'Customer-focused radical benchmark', '2004-07-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Sebastian Tynewell', 'stynewellb5', 'stynewellb5@mtv.com', 'zA7)OXqeJ91soesj', '5354927211', 'https://robohash.org/idmagnimolestiae.png?size=50x50&set=set1', 'Multi-layered contextually-based extranet', '1972-04-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Helena Thorns', 'hthornsb6', 'hthornsb6@acquirethisname.com', 'lZ1*stJ''.s', '1479125584', 'https://robohash.org/magnialiasveritatis.png?size=50x50&set=set1', 'Monitored value-added architecture', '1999-11-02', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Carney Bidnall', 'cbidnallb7', 'cbidnallb7@arizona.edu', 'jJ7~nt6$O_W''.*', '5607955839', 'https://robohash.org/corporisetfugit.png?size=50x50&set=set1', 'Team-oriented web-enabled hierarchy', '1986-04-01', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jozef Conen', 'jconenb8', 'jconenb8@sakura.ne.jp', 'xW6\+1G=C2$,`?H', '2767163853', 'https://robohash.org/quibusdamdictaconsequatur.png?size=50x50&set=set1', 'Phased explicit software', '1983-03-28', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Elisabetta Ledwich', 'eledwichb9', 'eledwichb9@deliciousdays.com', 'rJ8&ENWnJVWwIyM', '3664922542', 'https://robohash.org/temporemaioreslaudantium.png?size=50x50&set=set1', 'Robust 6th generation application', '1983-03-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tobi Donneely', 'tdonneelyba', 'tdonneelyba@yelp.com', 'tJ2\ID=Q', '6852523299', 'https://robohash.org/estestlaborum.png?size=50x50&set=set1', 'User-centric bandwidth-monitored groupware', '1976-06-24', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Nicko Mertel', 'nmertelbb', 'nmertelbb@posterous.com', 'wC1$?r#5vXU+=', '4645432072', 'https://robohash.org/erroraccusantiumdebitis.png?size=50x50&set=set1', 'Reactive national matrix', '1983-12-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ricky Bikker', 'rbikkerbc', 'rbikkerbc@sohu.com', 'iB7~y~W`B49''T@', '5776936630', 'https://robohash.org/abipsamperferendis.png?size=50x50&set=set1', 'Enhanced optimal application', '1996-04-25', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Meredith Ketts', 'mkettsbd', 'mkettsbd@sciencedaily.com', 'tT5}eM!Q|', '6012695468', 'https://robohash.org/utnondoloribus.png?size=50x50&set=set1', 'Re-contextualized cohesive system engine', '1991-12-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Darya Harrington', 'dharringtonbe', 'dharringtonbe@csmonitor.com', 'kK5)hQ/D_v', '4362845039', 'https://robohash.org/eiusestdoloremque.png?size=50x50&set=set1', 'Networked incremental help-desk', '2000-02-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Wenda Kemp', 'wkempbf', 'wkempbf@desdev.cn', 'xF6#D5y7&K&', '2416206691', 'https://robohash.org/utperferendisexpedita.png?size=50x50&set=set1', 'Enhanced asynchronous forecast', '1997-09-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Heda Delacourt', 'hdelacourtbg', 'hdelacourtbg@stanford.edu', 'rA7/=BzUdC5Ghqq', '8498277948', 'https://robohash.org/autquovoluptatem.png?size=50x50&set=set1', 'Public-key web-enabled attitude', '1999-06-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Annabell Creebo', 'acreebobh', 'acreebobh@bloomberg.com', 'mS4!GV#i70u', '3478712519', 'https://robohash.org/saepeofficiisautem.png?size=50x50&set=set1', 'Virtual 6th generation encryption', '1983-04-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Felice Kingescot', 'fkingescotbi', 'fkingescotbi@slideshare.net', 'rM8)''+Eliqa', '9263125711', 'https://robohash.org/vitaeasperioresmolestiae.png?size=50x50&set=set1', 'Business-focused eco-centric hub', '1979-11-26', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cornie Pacey', 'cpaceybj', 'cpaceybj@cargocollective.com', 'dF3=S,x!cA>''!ZM', '3287864457', 'https://robohash.org/quosipsamsed.png?size=50x50&set=set1', 'Business-focused hybrid groupware', '1993-08-28', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dante Cranmore', 'dcranmorebk', 'dcranmorebk@drupal.org', 'vW1.3=h\,0?', '5255418466', 'https://robohash.org/sitconsequaturaut.png?size=50x50&set=set1', 'Function-based non-volatile workforce', '2003-01-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lula Ryce', 'lrycebl', 'lrycebl@thetimes.co.uk', 'jS6<BTq9FvFr', '5345667807', 'https://robohash.org/adolorescumque.png?size=50x50&set=set1', 'Centralized foreground hardware', '1994-12-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jean Tilley', 'jtilleybm', 'jtilleybm@irs.gov', 'cA2\1~!O', '7077862969', 'https://robohash.org/veritatisquisvoluptatem.png?size=50x50&set=set1', 'Secured optimizing protocol', '2001-01-26', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Reiko Atty', 'rattybn', 'rattybn@cyberchimps.com', 'tW0|e#h%\ytFCB*5', '2429698473', 'https://robohash.org/uteteveniet.png?size=50x50&set=set1', 'Devolved discrete policy', '1983-07-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Diana Francescuzzi', 'dfrancescuzzibo', 'dfrancescuzzibo@ifeng.com', 'cI0?B{nbj2!x)6', '6608626573', 'https://robohash.org/necessitatibusvoluptasrerum.png?size=50x50&set=set1', 'Polarised solution-oriented monitoring', '1996-08-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Nikolas Daffern', 'ndaffernbp', 'ndaffernbp@google.com', 'oJ0`,,#$', '8249858922', 'https://robohash.org/quisedvel.png?size=50x50&set=set1', 'Polarised global adapter', '1996-05-20', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Terrijo Dymidowski', 'tdymidowskibq', 'tdymidowskibq@godaddy.com', 'bC9@VR|tE%NpmQ', '5975585853', 'https://robohash.org/nostrumeaa.png?size=50x50&set=set1', 'Multi-channelled modular installation', '1970-11-26', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Hallsy Jori', 'hjoribr', 'hjoribr@globo.com', 'hC1@7jqi', '4102540985', 'https://robohash.org/nisisitquas.png?size=50x50&set=set1', 'Multi-tiered systemic architecture', '1980-03-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Idaline Ericssen', 'iericssenbs', 'iericssenbs@ustream.tv', 'xE0~V84SE', '1066408677', 'https://robohash.org/dolorepraesentiumducimus.png?size=50x50&set=set1', 'Intuitive impactful customer loyalty', '2002-06-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dodie Pearsall', 'dpearsallbt', 'dpearsallbt@yahoo.co.jp', 'mY2##oL\"xkqrl', '9274856010', 'https://robohash.org/autquiomnis.png?size=50x50&set=set1', 'Universal neutral help-desk', '1978-06-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Clarette Dudmesh', 'cdudmeshbu', 'cdudmeshbu@yellowpages.com', 'gL2/CI4adji', '1691732582', 'https://robohash.org/fugaveniamab.png?size=50x50&set=set1', 'Focused web-enabled product', '1986-03-02', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Earle Ottewill', 'eottewillbv', 'eottewillbv@google.ru', 'cX5"dQ+op`LwROn', '8673489023', 'https://robohash.org/evenietadbeatae.png?size=50x50&set=set1', 'Upgradable intermediate throughput', '2005-04-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dulciana Berthe', 'dberthebw', 'dberthebw@istockphoto.com', 'hP2!>{JU!|', '6713471630', 'https://robohash.org/magninonmodi.png?size=50x50&set=set1', 'Versatile intangible flexibility', '1992-07-26', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Page Chesshyre', 'pchesshyrebx', 'pchesshyrebx@weather.com', 'cC0''vF&I', '9078532140', 'https://robohash.org/temporibusearumvoluptatem.png?size=50x50&set=set1', 'Open-architected real-time portal', '2002-12-27', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Maria Whitton', 'mwhittonby', 'mwhittonby@flickr.com', 'hX0|oo6dz.tU!3%', '3097103863', 'https://robohash.org/minimaundenihil.png?size=50x50&set=set1', 'Proactive attitude-oriented capacity', '1993-08-05', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Vaughn Iiannone', 'viiannonebz', 'viiannonebz@china.com.cn', 'xV7~wy''O', '3988792890', 'https://robohash.org/quiaeosconsequuntur.png?size=50x50&set=set1', 'Synchronised discrete portal', '1993-09-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cleon Parkins', 'cparkinsc0', 'cparkinsc0@bandcamp.com', 'kV9#1Y?q|?z5', '6478982458', 'https://robohash.org/debitisassumendalaboriosam.png?size=50x50&set=set1', 'Implemented fresh-thinking installation', '2000-03-14', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Elsinore Ecclesall', 'eecclesallc1', 'eecclesallc1@list-manage.com', 'jX9''3*GD%VOfcuS', '9127830917', 'https://robohash.org/easuscipitmolestias.png?size=50x50&set=set1', 'Networked mission-critical help-desk', '1992-01-18', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Odelia Whellans', 'owhellansc2', 'owhellansc2@drupal.org', 'iK6!e}NA3N)', '1117276293', 'https://robohash.org/fugautofficiis.png?size=50x50&set=set1', 'Integrated demand-driven paradigm', '1995-06-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Even Emblin', 'eemblinc3', 'eemblinc3@instagram.com', 'qI8.!?!=WO', '5469863330', 'https://robohash.org/doloremqueexcepturiut.png?size=50x50&set=set1', 'Reactive real-time instruction set', '2005-05-16', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Darnell Clout', 'dcloutc4', 'dcloutc4@ebay.com', 'mJ5,*`2!=', '8153888030', 'https://robohash.org/natusseddebitis.png?size=50x50&set=set1', 'Reactive context-sensitive synergy', '2000-04-04', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Iona McKinnon', 'imckinnonc5', 'imckinnonc5@github.io', 'jF9/Tlcpb+M1', '3829379140', 'https://robohash.org/aperiamquibusdamipsam.png?size=50x50&set=set1', 'Open-architected zero tolerance open architecture', '1971-08-28', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Lianna Cadell', 'lcadellc6', 'lcadellc6@macromedia.com', 'bY7+A`I#l1G', '1477628670', 'https://robohash.org/intemporepossimus.png?size=50x50&set=set1', 'User-friendly multi-tasking product', '1977-04-27', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Mendy Bloschke', 'mbloschkec7', 'mbloschkec7@ihg.com', 'gD1}N,&FGB0', '6474483050', 'https://robohash.org/distinctiofugaut.png?size=50x50&set=set1', 'Diverse next generation methodology', '1990-05-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Alvera Lakin', 'alakinc8', 'alakinc8@dyndns.org', 'pZ4@}4aCU', '1069540701', 'https://robohash.org/etexet.png?size=50x50&set=set1', 'Future-proofed background model', '1974-08-21', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Halley Mowen', 'hmowenc9', 'hmowenc9@pcworld.com', 'wH9?<&zM{._R', '3194415880', 'https://robohash.org/isteetvitae.png?size=50x50&set=set1', 'Mandatory cohesive product', '1994-04-29', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Blakeley Dulanty', 'bdulantyca', 'bdulantyca@slideshare.net', 'uG0?W+pv$`r', '2228133586', 'https://robohash.org/debitisquisdolorem.png?size=50x50&set=set1', 'Organized national model', '1978-03-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Alano Rushbury', 'arushburycb', 'arushburycb@paginegialle.it', 'tE6?l}P%', '5858212209', 'https://robohash.org/quiseligenditempore.png?size=50x50&set=set1', 'Re-engineered 24/7 capability', '2003-04-09', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Willie Tarbert', 'wtarbertcc', 'wtarbertcc@wikimedia.org', 'sW5''SrM?Q', '3051039782', 'https://robohash.org/dictaomnisminus.png?size=50x50&set=set1', 'Multi-channelled optimal array', '1997-02-22', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Linn Foynes', 'lfoynescd', 'lfoynescd@booking.com', 'aM3?qGFxe8*w''', '9787812282', 'https://robohash.org/numquammagnivoluptas.png?size=50x50&set=set1', 'Re-engineered non-volatile utilisation', '1976-11-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Raff Moulsdall', 'rmoulsdallce', 'rmoulsdallce@epa.gov', 'kB2><z%@+{wk&q_L', '3773543450', 'https://robohash.org/estaliquamveritatis.png?size=50x50&set=set1', 'Profit-focused optimal superstructure', '1980-03-30', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Carola MacAdam', 'cmacadamcf', 'cmacadamcf@berkeley.edu', 'aA7|p=%pAPWY+', '8585905174', 'https://robohash.org/maioresvoluptaseaque.png?size=50x50&set=set1', 'Secured multimedia projection', '1971-05-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Susanne Geary', 'sgearycg', 'sgearycg@t-online.de', 'mJ5$''''_zr?KX5lf$', '1119264804', 'https://robohash.org/utaliquamet.png?size=50x50&set=set1', 'Multi-channelled solution-oriented info-mediaries', '1992-08-19', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Bev Mumberson', 'bmumbersonch', 'bmumbersonch@skyrock.com', 'zK2\=pXK,+7,ca', '7707220220', 'https://robohash.org/quooditdeserunt.png?size=50x50&set=set1', 'Robust impactful artificial intelligence', '1999-01-06', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gabie Greatland', 'ggreatlandci', 'ggreatlandci@blogger.com', 'vD5&V$~J', '5616532801', 'https://robohash.org/itaquererumducimus.png?size=50x50&set=set1', 'Cross-platform national architecture', '1997-12-29', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Terry Broose', 'tbroosecj', 'tbroosecj@walmart.com', 'iN7(W_n>*@Rn#@1\', '2383621702', 'https://robohash.org/eumeadeserunt.png?size=50x50&set=set1', 'Switchable directional website', '2004-11-18', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Wrennie Kynastone', 'wkynastoneck', 'wkynastoneck@unesco.org', 'dW8+Y.{EGIfr@', '3374678471', 'https://robohash.org/autinvel.png?size=50x50&set=set1', 'Intuitive client-driven framework', '1995-08-22', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Madelin Minchella', 'mminchellacl', 'mminchellacl@skype.com', 'aN1`nKS`>\DGPK"', '6052009200', 'https://robohash.org/delectusrepudiandaedeleniti.png?size=50x50&set=set1', 'Reactive leading edge knowledge user', '1980-10-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Thayne Gillise', 'tgillisecm', 'tgillisecm@umich.edu', 'eK7\5sZo*U''', '4233540802', 'https://robohash.org/minusveldolor.png?size=50x50&set=set1', 'Managed holistic archive', '2002-11-13', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Darryl Eam', 'deamcn', 'deamcn@sourceforge.net', 'kM4.X*l{yExC', '6588553846', 'https://robohash.org/omnissaepeeos.png?size=50x50&set=set1', 'Cross-group hybrid budgetary management', '1996-01-18', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jemimah Lightbowne', 'jlightbowneco', 'jlightbowneco@hubpages.com', 'hE8+}vtgUn', '9688121672', 'https://robohash.org/eligendiconsecteturalias.png?size=50x50&set=set1', 'Innovative upward-trending product', '1985-09-12', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Carson Roberti', 'croberticp', 'croberticp@blogspot.com', 'cL6|P.X9M', '5805386506', 'https://robohash.org/nemoquiaet.png?size=50x50&set=set1', 'Balanced client-driven projection', '1970-11-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Benedetta Skellon', 'bskelloncq', 'bskelloncq@house.gov', 'wO1%BCStpinu', '9454653669', 'https://robohash.org/nesciuntvoluptasveritatis.png?size=50x50&set=set1', 'Open-source multimedia standardization', '1986-10-30', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gracie Simoes', 'gsimoescr', 'gsimoescr@pcworld.com', 'uO8_3loC89n1#', '2042905853', 'https://robohash.org/sedsolutaquis.png?size=50x50&set=set1', 'Automated disintermediate productivity', '1974-05-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Agustin Willey', 'awilleycs', 'awilleycs@ucoz.ru', 'hI9>}8zWP#', '1241942072', 'https://robohash.org/eiusquoreiciendis.png?size=50x50&set=set1', 'Quality-focused tangible conglomeration', '2001-10-05', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Moll Garford', 'mgarfordct', 'mgarfordct@nydailynews.com', 'mN8&eK#vN', '5342627660', 'https://robohash.org/natusaliquaminventore.png?size=50x50&set=set1', 'Customizable national monitoring', '1976-10-25', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Tobie Dunstan', 'tdunstancu', 'tdunstancu@g.co', 'qT4&=|LGcT2Pf(', '8575840065', 'https://robohash.org/voluptatemdolorvoluptas.png?size=50x50&set=set1', 'Visionary coherent circuit', '1985-08-18', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ronny Eckford', 'reckfordcv', 'reckfordcv@state.gov', 'nL4~&r)er', '4559813151', 'https://robohash.org/minusrepellenduseos.png?size=50x50&set=set1', 'Business-focused uniform open system', '1992-05-16', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Burton Gauden', 'bgaudencw', 'bgaudencw@ihg.com', 'mL2=N}e"mCCw', '5393813245', 'https://robohash.org/repellatasperioresipsa.png?size=50x50&set=set1', 'Function-based 5th generation moderator', '1994-03-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Addie Grishechkin', 'agrishechkincx', 'agrishechkincx@etsy.com', 'eS8\oidZq>Pgg?I', '2929243337', 'https://robohash.org/consequaturconsequunturarchitecto.png?size=50x50&set=set1', 'Vision-oriented bifurcated website', '1986-06-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Harriott Gaish', 'hgaishcy', 'hgaishcy@imdb.com', 'zU2<KM1O', '9748625951', 'https://robohash.org/eummagnameos.png?size=50x50&set=set1', 'Organized disintermediate implementation', '1972-10-07', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Teriann Mucklow', 'tmucklowcz', 'tmucklowcz@forbes.com', 'aI0{*F8WNh#I/G', '7211698802', 'https://robohash.org/accusamusomnisnon.png?size=50x50&set=set1', 'Managed responsive service-desk', '1991-08-05', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Kassey Byrch', 'kbyrchd0', 'kbyrchd0@google.com.br', 'yQ6=}~pHwP)UY3k', '6602736645', 'https://robohash.org/velaliasiure.png?size=50x50&set=set1', 'Horizontal homogeneous archive', '2004-07-11', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Faustine Fehners', 'ffehnersd1', 'ffehnersd1@ycombinator.com', 'zD6>O,"{''S2', '1692693582', 'https://robohash.org/eumfugitrerum.png?size=50x50&set=set1', 'Multi-tiered object-oriented strategy', '1986-11-04', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Alfonso Enderle', 'aenderled2', 'aenderled2@over-blog.com', 'gI7}3QmC!Ir"x99s', '4392140644', 'https://robohash.org/voluptateestad.png?size=50x50&set=set1', 'Progressive modular service-desk', '1978-01-07', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Randal Tomasi', 'rtomasid3', 'rtomasid3@trellian.com', 'nT6<+2D#I|d''8', '4943072167', 'https://robohash.org/dictaquaedoloribus.png?size=50x50&set=set1', 'Down-sized user-facing policy', '1971-05-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Ranice Swarbrick', 'rswarbrickd4', 'rswarbrickd4@ihg.com', 'hA0*j++=', '7038793457', 'https://robohash.org/quiaeadolorum.png?size=50x50&set=set1', 'Integrated stable focus group', '2003-05-06', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Dane Lenglet', 'dlengletd5', 'dlengletd5@nyu.edu', 'bZ6$W/6#GTJ', '6681999533', 'https://robohash.org/aspernaturnamet.png?size=50x50&set=set1', 'Stand-alone clear-thinking benchmark', '1990-07-03', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Conchita Scolding', 'cscoldingd6', 'cscoldingd6@independent.co.uk', 'bD1?X/hikU=_g', '4268084588', 'https://robohash.org/laudantiumquieos.png?size=50x50&set=set1', 'Digitized interactive open architecture', '1976-12-11', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Rhodia McGeagh', 'rmcgeaghd7', 'rmcgeaghd7@flavors.me', 'xY0}g0RH+5%73''P', '5081953013', 'https://robohash.org/explicaboipsamomnis.png?size=50x50&set=set1', 'Distributed non-volatile utilisation', '1988-08-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Earle Lyptratt', 'elyptrattd8', 'elyptrattd8@slideshare.net', 'dB0\KE$40\gZHv!}', '8794237736', 'https://robohash.org/quianimiodio.png?size=50x50&set=set1', 'Persevering didactic hub', '1988-05-24', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Reagen Trillo', 'rtrillod9', 'rtrillod9@bandcamp.com', 'jG7!D`r''&AKZNh', '8686034810', 'https://robohash.org/suntvelsed.png?size=50x50&set=set1', 'Fully-configurable user-facing core', '1996-03-21', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Flynn Innot', 'finnotda', 'finnotda@washington.edu', 'jL9''D{<.fW.', '5939124949', 'https://robohash.org/officiislaborumsunt.png?size=50x50&set=set1', 'Organic multi-state contingency', '1998-10-02', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Geoff McMurty', 'gmcmurtydb', 'gmcmurtydb@amazon.com', 'mW6~1T+B', '8014884686', 'https://robohash.org/nondelenitisint.png?size=50x50&set=set1', 'Synergized stable budgetary management', '1984-03-31', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Forest Scaife', 'fscaifedc', 'fscaifedc@ftc.gov', 'rR8/?xcmr~E', '1138477788', 'https://robohash.org/minimacorporismagni.png?size=50x50&set=set1', 'Reactive mobile info-mediaries', '1985-05-27', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Fayina Edmonston', 'fedmonstondd', 'fedmonstondd@independent.co.uk', 'dJ7\$J4p', '5096593437', 'https://robohash.org/suntquibusdamut.png?size=50x50&set=set1', 'Optimized object-oriented moratorium', '1988-07-13', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Elsey Pappi', 'epappide', 'epappide@merriam-webster.com', 'zB5.r0L+fOo)I$zo', '1667120780', 'https://robohash.org/veritatisautemeaque.png?size=50x50&set=set1', 'Customer-focused intangible concept', '1986-07-03', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Peta Pearsey', 'ppearseydf', 'ppearseydf@desdev.cn', 'uI7,E!X2_3}$HgN', '5431784704', 'https://robohash.org/assumendadolorpariatur.png?size=50x50&set=set1', 'Robust responsive projection', '1978-04-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Doralynne Truss', 'dtrussdg', 'dtrussdg@columbia.edu', 'wS1&vG5cX|PXv4', '4994946586', 'https://robohash.org/saepenonin.png?size=50x50&set=set1', 'Secured real-time hierarchy', '1998-09-05', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jess Delph', 'jdelphdh', 'jdelphdh@pbs.org', 'oW5\aIT.|4(0Z', '3447579479', 'https://robohash.org/occaecaticorporislaudantium.png?size=50x50&set=set1', 'Digitized zero tolerance adapter', '1985-09-29', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Chev D''eye', 'cdeyedi', 'cdeyedi@time.com', 'xF6>RB3GL@Q', '5571772988', 'https://robohash.org/natuseumfacilis.png?size=50x50&set=set1', 'Robust solution-oriented application', '1984-11-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Timmie Red', 'treddj', 'treddj@businessinsider.com', 'rB4</''H~$RAG}@', '1977553590', 'https://robohash.org/verovitaeunde.png?size=50x50&set=set1', 'Multi-lateral attitude-oriented structure', '2002-06-10', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Franzen Barents', 'fbarentsdk', 'fbarentsdk@cbsnews.com', 'pO2~V=EUL~(EQDt', '4974146653', 'https://robohash.org/ipsamassumendarem.png?size=50x50&set=set1', 'Vision-oriented content-based open architecture', '1990-03-27', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Mary Dinning', 'mdinningdl', 'mdinningdl@bloglines.com', 'lM8''u~d5e\s', '9433368286', 'https://robohash.org/cumquedebitisbeatae.png?size=50x50&set=set1', 'Implemented non-volatile productivity', '1972-02-07', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Keven Moan', 'kmoandm', 'kmoandm@pen.io', 'jZ1,S(r,=HAM0u&', '8954424529', 'https://robohash.org/atqueexplicaboid.png?size=50x50&set=set1', 'Centralized local architecture', '2000-02-08', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Estell Mugleston', 'emuglestondn', 'emuglestondn@miitbeian.gov.cn', 'qC5#dR"5hN2$RR%J', '2744490765', 'https://robohash.org/aspernaturquirecusandae.png?size=50x50&set=set1', 'Future-proofed optimal architecture', '2001-08-28', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Roxy Jonin', 'rjonindo', 'rjonindo@usda.gov', 'tB2).`7w`3JOa', '1883562447', 'https://robohash.org/similiqueetvelit.png?size=50x50&set=set1', 'Ergonomic bottom-line Graphical User Interface', '1996-04-28', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Gillian Oultram', 'goultramdp', 'goultramdp@npr.org', 'dV8&|hwt9+w30', '1172473093', 'https://robohash.org/voluptatemundeblanditiis.png?size=50x50&set=set1', 'Proactive needs-based toolset', '2003-09-01', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Shawn Liptrod', 'sliptroddq', 'sliptroddq@latimes.com', 'iR4{sBn~#Lioe', '4128924300', 'https://robohash.org/ducimusofficiaet.png?size=50x50&set=set1', 'Exclusive real-time knowledge base', '1980-01-15', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Cristin Brands', 'cbrandsdr', 'cbrandsdr@privacy.gov.au', 'hA6''0pzr', '6801683102', 'https://robohash.org/placeatutassumenda.png?size=50x50&set=set1', 'Intuitive bandwidth-monitored system engine', '1997-02-12', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Jeanna Yanshonok', 'jyanshonokds', 'jyanshonokds@slashdot.org', 'uP3{P8}y{G*(', '5502471807', 'https://robohash.org/nequevelitest.png?size=50x50&set=set1', 'Organic clear-thinking forecast', '2001-05-03', true);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Carin Cleatherow', 'ccleatherowdt', 'ccleatherowdt@sciencedirect.com', 'bY2}XU<pN9xx', '6295075712', 'https://robohash.org/perferendisautcum.png?size=50x50&set=set1', 'Fully-configurable tangible extranet', '1980-12-15', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Penelopa Potteril', 'ppotterildu', 'ppotterildu@mediafire.com', 'tT3\?=hbjT8cK"', '6347421606', 'https://robohash.org/facereestarchitecto.png?size=50x50&set=set1', 'Monitored motivating contingency', '1975-09-23', false);
insert into users (name, username, email, password, phone_number, profile_picture, description, birth_date, public_profile) values ('Carilyn Riseborough', 'criseboroughdv', 'criseboroughdv@cafepress.com', 'uN5''&tgO{UY}', '7166637630', 'https://robohash.org/essequisquamdebitis.png?size=50x50&set=set1', 'Organic bandwidth-monitored neural-net', '1988-03-11', true);


/* Groups */

insert into groups (name, description, public_group, date) values ('Hauck-Bailey', 'Exclusive disintermediate database', false, '2022-12-07');
insert into groups (name, description, public_group, date) values ('Wehner Inc', 'Programmable 4th generation project', true, '2022-10-30');
insert into groups (name, description, public_group, date) values ('Harris-Kihn', 'Reverse-engineered incremental open architecture', true, '2022-12-12');
insert into groups (name, description, public_group, date) values ('Hermiston-Schmitt', 'Cloned fresh-thinking moratorium', true, '2022-11-20');
insert into groups (name, description, public_group, date) values ('Botsford Group', 'Progressive web-enabled installation', false, '2022-11-28');
insert into groups (name, description, public_group, date) values ('Cassin-McGlynn', 'Re-engineered coherent initiative', false, '2022-12-19');
insert into groups (name, description, public_group, date) values ('Smitham, Reilly and Borer', 'Visionary incremental local area network', false, '2022-11-11');
insert into groups (name, description, public_group, date) values ('Bechtelar, Schneider and Greenholt', 'Adaptive real-time encryption', true, '2022-11-24');
insert into groups (name, description, public_group, date) values ('Weissnat-Hyatt', 'Adaptive asynchronous throughput', false, '2022-10-23');
insert into groups (name, description, public_group, date) values ('Medhurst, Sporer and Labadie', 'Advanced cohesive circuit', false, '2022-11-08');
insert into groups (name, description, public_group, date) values ('Cassin Inc', 'Extended bi-directional access', true, '2022-12-07');
insert into groups (name, description, public_group, date) values ('Kreiger Group', 'Organized 3rd generation secured line', false, '2022-11-28');
insert into groups (name, description, public_group, date) values ('Purdy Inc', 'Re-contextualized didactic project', false, '2022-12-10');
insert into groups (name, description, public_group, date) values ('Schimmel-Barton', 'Cloned background open architecture', false, '2022-12-07');
insert into groups (name, description, public_group, date) values ('Pacocha Group', 'Organized multi-state hub', true, '2022-12-11');
insert into groups (name, description, public_group, date) values ('Christiansen and Sons', 'Centralized user-facing migration', false, '2022-10-25');
insert into groups (name, description, public_group, date) values ('Ebert-Kirlin', 'Customizable well-modulated implementation', true, '2022-11-17');
insert into groups (name, description, public_group, date) values ('Simonis, Swift and Johns', 'Sharable system-worthy website', true, '2022-12-04');
insert into groups (name, description, public_group, date) values ('Nolan LLC', 'Intuitive 24/7 parallelism', true, '2022-10-31');
insert into groups (name, description, public_group, date) values ('Reinger-Lakin', 'Cross-platform dedicated throughput', false, '2022-11-02');


/* Owns */

insert into owns (user_id, group_id, date) values (489, 11, '10/30/2022');
insert into owns (user_id, group_id, date) values (11, 12, '10/30/2022');
insert into owns (user_id, group_id, date) values (225, 6, '11/3/2022');
insert into owns (user_id, group_id, date) values (69, 1, '12/30/2022');
insert into owns (user_id, group_id, date) values (451, 2, '10/26/2022');
insert into owns (user_id, group_id, date) values (128, 4, '12/20/2022');
insert into owns (user_id, group_id, date) values (269, 10, '11/24/2022');
insert into owns (user_id, group_id, date) values (68, 11, '11/12/2022');
insert into owns (user_id, group_id, date) values (138, 15, '10/28/2022');
insert into owns (user_id, group_id, date) values (361, 7, '12/17/2022');
insert into owns (user_id, group_id, date) values (479, 4, '11/10/2022');
insert into owns (user_id, group_id, date) values (70, 3, '12/28/2022');
insert into owns (user_id, group_id, date) values (25, 12, '10/28/2022');
insert into owns (user_id, group_id, date) values (445, 5, '12/4/2022');
insert into owns (user_id, group_id, date) values (252, 14, '12/9/2022');
insert into owns (user_id, group_id, date) values (49, 14, '11/28/2022');
insert into owns (user_id, group_id, date) values (393, 10, '10/27/2022');
insert into owns (user_id, group_id, date) values (400, 6, '12/25/2022');
insert into owns (user_id, group_id, date) values (440, 11, '11/6/2022');
insert into owns (user_id, group_id, date) values (493, 2, '11/27/2022');
insert into owns (user_id, group_id, date) values (182, 4, '10/24/2022');
insert into owns (user_id, group_id, date) values (465, 7, '12/16/2022');
insert into owns (user_id, group_id, date) values (400, 14, '12/2/2022');
insert into owns (user_id, group_id, date) values (496, 4, '12/27/2022');
insert into owns (user_id, group_id, date) values (8, 3, '12/20/2022');
insert into owns (user_id, group_id, date) values (288, 10, '11/14/2022');
insert into owns (user_id, group_id, date) values (331, 12, '11/14/2022');
insert into owns (user_id, group_id, date) values (72, 8, '12/18/2022');
insert into owns (user_id, group_id, date) values (304, 2, '12/27/2022');
insert into owns (user_id, group_id, date) values (24, 9, '11/30/2022');
insert into owns (user_id, group_id, date) values (430, 3, '12/21/2022');
insert into owns (user_id, group_id, date) values (490, 2, '12/21/2022');
insert into owns (user_id, group_id, date) values (226, 6, '10/25/2022');
insert into owns (user_id, group_id, date) values (411, 14, '11/8/2022');
insert into owns (user_id, group_id, date) values (270, 9, '12/8/2022');
insert into owns (user_id, group_id, date) values (41, 9, '10/25/2022');
insert into owns (user_id, group_id, date) values (216, 10, '11/12/2022');
insert into owns (user_id, group_id, date) values (52, 14, '12/21/2022');
insert into owns (user_id, group_id, date) values (282, 10, '11/8/2022');
insert into owns (user_id, group_id, date) values (264, 10, '12/30/2022');



/*Posts*/
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (23, NULL, CURRENT_TIMESTAMP, 'Post by user 23.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (142, NULL, CURRENT_TIMESTAMP, 'Post by user 142.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (402, NULL, CURRENT_TIMESTAMP, 'Post by user 402.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (5, NULL, CURRENT_TIMESTAMP, 'Post by user 5.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (134, NULL, CURRENT_TIMESTAMP, 'Post by user 134.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (311, NULL, CURRENT_TIMESTAMP, 'Post by user 311.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (45, NULL, CURRENT_TIMESTAMP, 'Post by user 45.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (220, NULL, CURRENT_TIMESTAMP, 'Post by user 220.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (66, NULL, CURRENT_TIMESTAMP, 'Post by user 66.', true);
INSERT INTO posts (user_id, group_id, date, description, public_post) VALUES (489, NULL, CURRENT_TIMESTAMP, 'Post by user 489.', true);

insert into posts (user_id, group_id, date, description, public_post) values (329, null, '2022-11-03', 'Quisque ut erat. Curabitur gravida nisi at nibh. In hac habitasse platea dictumst. Aliquam augue quam, sollicitudin vitae, consectetuer eget, rutrum at, lorem. Integer tincidunt ante vel ipsum. Praesent blandit lacinia erat. Vestibulum sed magna at nunc commodo placerat.', false);
insert into posts (user_id, group_id, date, description, public_post) values (332, null, '2022-11-01', 'Donec quis orci eget orci vehicula condimentum. Curabitur in libero ut massa volutpat convallis. Morbi odio odio, elementum eu, interdum eu, tincidunt in, leo. Maecenas pulvinar lobortis est.', false);
insert into posts (user_id, group_id, date, description, public_post) values (313, null, '2022-12-12', 'Vivamus tortor. Duis mattis egestas metus. Aenean fermentum.', false);
insert into posts (user_id, group_id, date, description, public_post) values (445, null, '2022-11-05', 'Aliquam quis turpis eget elit sodales scelerisque.', true);
insert into posts (user_id, group_id, date, description, public_post) values (191, null, '2022-11-26', 'Integer non velit. Donec diam neque, vestibulum eget, vulputate ut, ultrices vel, augue.', true);
insert into posts (user_id, group_id, date, description, public_post) values (391, null, '2022-12-28', 'Pellentesque eget nunc. Donec quis orci eget orci vehicula condimentum. Curabitur in libero ut massa volutpat convallis. Morbi odio odio, elementum eu, interdum eu, tincidunt in, leo. Maecenas pulvinar lobortis est. Phasellus sit amet erat. Nulla tempus. Vivamus in felis eu sapien cursus vestibulum.', false);
insert into posts (user_id, group_id, date, description, public_post) values (12, null, '2022-11-05', 'Aenean lectus. Pellentesque eget nunc. Donec quis orci eget orci vehicula condimentum. Curabitur in libero ut massa volutpat convallis.', false);
insert into posts (user_id, group_id, date, description, public_post) values (97, null, '2022-11-04', 'Donec dapibus. Duis at velit eu est congue elementum.', true);
insert into posts (user_id, group_id, date, description, public_post) values (317, null, '2022-11-01', 'Morbi non lectus. Aliquam sit amet diam in magna bibendum imperdiet. Nullam orci pede, venenatis non, sodales sed, tincidunt eu, felis. Fusce posuere felis sed lacus. Morbi sem mauris, laoreet ut, rhoncus aliquet, pulvinar sed, nisl. Nunc rhoncus dui vel sem. Sed sagittis. Nam congue, risus semper porta volutpat, quam pede lobortis ligula, sit amet eleifend pede libero quis orci. Nullam molestie nibh in lectus. Pellentesque at nulla.', false);
insert into posts (user_id, group_id, date, description, public_post) values (434, null, '2022-12-01', 'Praesent blandit lacinia erat. Vestibulum sed magna at nunc commodo placerat. Praesent blandit. Nam nulla.', true);
insert into posts (user_id, group_id, date, description, public_post) values (250, null, '2022-12-06', 'Phasellus sit amet erat. Nulla tempus.', true);
insert into posts (user_id, group_id, date, description, public_post) values (182, null, '2022-11-02', 'Phasellus id sapien in sapien iaculis congue. Vivamus metus arcu, adipiscing molestie, hendrerit at, vulputate vitae, nisl. Aenean lectus. Pellentesque eget nunc. Donec quis orci eget orci vehicula condimentum. Curabitur in libero ut massa volutpat convallis. Morbi odio odio, elementum eu, interdum eu, tincidunt in, leo. Maecenas pulvinar lobortis est.', true);
insert into posts (user_id, group_id, date, description, public_post) values (490, null, '2022-11-20', 'Nulla mollis molestie lorem. Quisque ut erat. Curabitur gravida nisi at nibh. In hac habitasse platea dictumst. Aliquam augue quam, sollicitudin vitae, consectetuer eget, rutrum at, lorem. Integer tincidunt ante vel ipsum. Praesent blandit lacinia erat. Vestibulum sed magna at nunc commodo placerat. Praesent blandit. Nam nulla.', true);
insert into posts (user_id, group_id, date, description, public_post) values (68, null, '2022-10-24', 'In blandit ultrices enim. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin interdum mauris non ligula pellentesque ultrices. Phasellus id sapien in sapien iaculis congue. Vivamus metus arcu, adipiscing molestie, hendrerit at, vulputate vitae, nisl.', false);
insert into posts (user_id, group_id, date, description, public_post) values (482, null, '2022-12-15', 'Suspendisse accumsan tortor quis turpis.', true);

/*Is_friend*/

INSERT INTO is_friend (user_id, friend_id, date) VALUES (23, 89, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (142, 367, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (402, 15, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (5, 488, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (134, 254, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (311, 205, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (45, 97, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (220, 49, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (66, 330, CURRENT_TIMESTAMP);
INSERT INTO is_friend (user_id, friend_id, date) VALUES (489, 159, CURRENT_TIMESTAMP);

/*Comments*/

INSERT INTO comments (user_id, post_id, content, date) VALUES (89, 1, 'Comment from user 89.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (367, 2, 'Comment from user 367.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (15, 3, 'Comment from user 15.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (488, 4, 'Comment from user 488.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (254, 5, 'Comment from user 254.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (205, 6, 'Comment from user 205.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (97, 7, 'Comment from user 97.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (49, 8, 'Comment from user 49.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (330, 9, 'Comment from user 330.', CURRENT_TIMESTAMP);
INSERT INTO comments (user_id, post_id, content, date) VALUES (159, 10, 'Comment from user 159.', CURRENT_TIMESTAMP);


/* Likes */

INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (89, 1, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (367, 2, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (15, 3, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (488, 4, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (254, 5, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (205, 6, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (97, 7, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (49, 8, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (330, 9, NULL, CURRENT_TIMESTAMP);
INSERT INTO likes (user_id, post_id, comment_id, date) VALUES (159, 10, NULL, CURRENT_TIMESTAMP);
