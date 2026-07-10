CREATE TABLE investment
(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meme_id INT NOT NULL,
    donated_coins INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user (id),
    FOREIGN KEY (meme_id) REFERENCES meme (id)
);