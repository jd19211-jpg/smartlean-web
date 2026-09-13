ALTER TABLE leads
  ADD COLUMN meeting_booked TINYINT(1) NOT NULL DEFAULT 0 AFTER transcript,
  ADD COLUMN meeting_date DATE NULL AFTER meeting_booked,
  ADD COLUMN meeting_start VARCHAR(5) NULL AFTER meeting_date,
  ADD COLUMN meeting_end VARCHAR(5) NULL AFTER meeting_start,
  ADD COLUMN calendar_event_id VARCHAR(255) NULL AFTER meeting_end;
