-- Run once on an existing installation before deploying the inventory changes.
ALTER TABLE orders
    ADD COLUMN stock_reserved TINYINT(1) NOT NULL DEFAULT 0 AFTER notes;
