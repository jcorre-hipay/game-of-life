CREATE TABLE IF NOT EXISTS events
(
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    entity_id TEXT NOT NULL,
    type TEXT NOT NULL,
    date TEXT NOT NULL,
    data TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS colonies
(
    id TEXT PRIMARY KEY NOT NULL,
    generation INTEGER NOT NULL,
    width INTEGER NOT NULL,
    height INTEGER NOT NULL,
    creation_date TEXT NOT NULL,
    last_update_date TEXT NOT NULL
);
