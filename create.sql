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
Types' definition
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
    description VARCHAR(300),
    public_group BOOLEAN DEFAULT false,
    date DATE NOT NULL,
    CHECK (date >= CURRENT_DATE)
);

CREATE TABLE posts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ,
    group_id INTEGER REFERENCES groups(id)  ON DELETE CASCADE,
    date TIMESTAMP NOT NULL,
    CHECK (date >= CURRENT_TIMESTAMP),
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
    CHECK (date >= CURRENT_TIMESTAMP)

);

CREATE TABLE files (
    id SERIAL PRIMARY KEY,
    post_id INTEGER REFERENCES posts(id)  ON DELETE CASCADE,
    comment_id INTEGER REFERENCES comments(id)  ON DELETE CASCADE,
    CHECK (post_id IS NULL AND comment_id IS NOT NULL OR post_id IS NOT NULL AND comment_id IS NULL),
    title VARCHAR(50) NOT NULL,
    files BYTEA NOT NULL,
    date TIMESTAMP NOT NULL,
    CHECK (date >= CURRENT_TIMESTAMP)
);

CREATE TABLE likes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    post_id INTEGER REFERENCES posts(id)  ON DELETE CASCADE,
    comment_id INTEGER REFERENCES comments(id)  ON DELETE CASCADE,
    CONSTRAINT unique_like UNIQUE (user_id, post_id, comment_id),
    CHECK (post_id IS NULL AND comment_id IS NOT NULL OR post_id IS NOT NULL AND comment_id IS NULL),
    date TIMESTAMP NOT NULL,
    CHECK (date >= CURRENT_TIMESTAMP)
);

CREATE TABLE is_friend (
    user_id INTEGER NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    friend_id INTEGER NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    PRIMARY KEY (user_id, friend_id),
    CHECK (user_id <> friend_id),
    date TIMESTAMP NOT NULL,
    CHECK (date >= CURRENT_TIMESTAMP) 
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
    CHECK (date >= CURRENT_TIMESTAMP)
);

CREATE TABLE owns (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    date TIMESTAMP NOT NULL,
    CHECK (date >= CURRENT_TIMESTAMP)
);

CREATE TABLE group_blocks (
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    blocked_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    blocked_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
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
    CHECK (date >= CURRENT_TIMESTAMP)
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
    CHECK (date >= CURRENT_TIMESTAMP),
    field_type field_types NOT NULL
);

CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    sender_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiver_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    date TIMESTAMP NOT NULL,
    CHECK (date >= CURRENT_TIMESTAMP),
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
 * Triggers
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




CREATE OR REPLACE FUNCTION check_like_comment_validity() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.group_id IS NOT NULL) THEN
        -- Check if the user is a member of the group
        IF (NEW.user_id NOT IN (SELECT user_id FROM is_member WHERE group_id = NEW.group_id)) THEN
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
    EXECUTE PROCEDURE check_like_comment_validity();

CREATE TRIGGER check_comment_validity
    BEFORE INSERT OR UPDATE ON comments
    FOR EACH ROW
    EXECUTE PROCEDURE check_like_comment_validity();






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






CREATE OR REPLACE FUNCTION prevent_duplicate_friend_requests()
RETURNS TRIGGER AS $$
BEGIN
        
    IF (NEW.notification_type) = 'friend_request' THEN

        /* if the users are already friends, delete the notification */

        IF EXISTS (SELECT 1 
                   FROM is_friend 
                   WHERE (user_id = (SELECT sender_id 
                                     FROM notifications 
                                     WHERE id = NEW.notification_id) AND 
                          friend_id = (SELECT receiver_id 
                                       FROM notifications 
                                       WHERE id = NEW.notification_id))) THEN
                           
            DELETE FROM notifications
            WHERE id = NEW.notification_id;
                           
            RAISE EXCEPTION 'Users are already friends! Notification deleted.';
            
        END IF;

        /* if some user has already sent a friend request to the other user, delete the notification */

        IF EXISTS (
            SELECT 1
            FROM user_notifications
            JOIN notifications ON user_notifications.notification_id = notifications.id
            WHERE notification_type = 'friend_request'
            AND (
                (sender_id = (SELECT sender_id 
                            FROM notifications 
                            WHERE id = NEW.notification_id) 
                AND receiver_id = (SELECT receiver_id 
                                FROM notifications 
                                WHERE id = NEW.notification_id))
                OR
                (sender_id = (SELECT receiver_id 
                            FROM notifications 
                            WHERE id = NEW.notification_id) 
                AND receiver_id = (SELECT sender_id 
                                FROM notifications 
                                WHERE id = NEW.notification_id))
            )
        ) THEN
            DELETE FROM notifications
            WHERE id = NEW.notification_id;
            RAISE EXCEPTION 'Friend request already exists! Notification deleted.';
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;



CREATE TRIGGER trigger_prevent_duplicate_friend_requests
BEFORE INSERT OR UPDATE ON user_notifications
FOR EACH ROW
EXECUTE FUNCTION prevent_duplicate_friend_requests();




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





CREATE OR REPLACE FUNCTION check_user_group_membership()
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


CREATE TRIGGER trigger_check_user_group_membership
BEFORE INSERT ON posts
FOR EACH ROW
EXECUTE FUNCTION check_user_group_membership();


-- Delete notification if user accepted friend request
CREATE OR REPLACE FUNCTION delete_friendship_notification()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM user_notifications JOIN notifications 
                ON user_notifications.notification_id = notifications.id WHERE notification_type = 'friend_request' 
                AND sender_id = NEW.user_id AND receiver_id = NEW.friend_id) THEN
        DELETE FROM notifications WHERE id = (SELECT notification_id FROM user_notifications JOIN notifications 
                                                ON user_notifications.notification_id = notifications.id 
                                                WHERE notification_type = 'friend_request' 
                                                AND sender_id = NEW.user_id 
                                                AND receiver_id = NEW.friend_id);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_delete_friendship_notification
AFTER INSERT ON is_friend
FOR EACH ROW
EXECUTE FUNCTION delete_friendship_notification();


-- Delete notification if user accepted join request
CREATE OR REPLACE FUNCTION delete_join_request_notification()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM group_notifications JOIN notifications 
                ON group_notifications.notification_id = notifications.id WHERE notification_type = 'join_request' 
                AND group_id = NEW.group_id AND receiver_id = NEW.user_id) THEN
        DELETE FROM notifications WHERE id = (SELECT notification_id FROM group_notifications JOIN notifications 
                                                ON group_notifications.notification_id = notifications.id 
                                                WHERE notification_type = 'join_request' 
                                                AND group_id = NEW.group_id 
                                                AND receiver_id = NEW.user_id);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_delete_join_request_notification
AFTER INSERT ON is_member
FOR EACH ROW
EXECUTE FUNCTION delete_join_request_notification();


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


