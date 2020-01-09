SELECT * FROM "foo" WHERE "flower" = 'Sakura';
-- Below is a string contains ';'
INSERT INTO "foo" ("id", "flower", "title") VALUES (1, 'Sakura', 'S; ku; ra');
-- Empty line
;
# Hash comments
INSERT INTO "foo" ("id", "flower", "title") VALUES (2, 'Rose', 'Rose');
