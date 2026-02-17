CREATE TABLE drugs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    generic_name VARCHAR(255),
    strength VARCHAR(100),               -- 500mg, 250mg/5ml
    dosage_form ENUM(
        'tablet','capsule','syrup','injection',
        'ointment','cream','drops','inhaler'
    ) NOT NULL,

    base_unit VARCHAR(50) NOT NULL,       -- tablet, ml, vial
    sale_unit VARCHAR(50) NOT NULL,       -- strip, bottle
    unit_conversion INT DEFAULT 1,        -- e.g. 1 strip = 10 tablets

    reorder_level INT DEFAULT 10,         -- alert threshold
    status TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
