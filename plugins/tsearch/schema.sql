/* BE CARREFUL OF PREFIX!! */


BEGIN;
/* NEW dc_post.post_idx field
======================================================== */
ALTER TABLE dc_post ADD COLUMN post_idx tsearch2.tsvector null;

/* NEW dc_comment.comment_idx field
======================================================== */
ALTER TABLE dc_comment ADD COLUMN comment_idx tsearch2.tsvector null;

/* TSEARCH2 functions
======================================================== */
--
-- Get tsearch2 vectors of text
--
CREATE OR REPLACE FUNCTION get_vectors(varchar,varchar)
	RETURNS tsearch2.tsvector AS
$body$
DECLARE
	i_subject ALIAS FOR $1;
	i_content ALIAS FOR $2;
BEGIN
	RETURN 
	tsearch2.concat(
		tsearch2.setweight(tsearch2.to_tsvector('default',i_subject),'A'),
		tsearch2.to_tsvector('default',i_content)
	);
END;
$body$
LANGUAGE plpgsql;


--
-- Find post_id with query
--
--DROP TYPE find_post_type CASCADE;
CREATE TYPE find_post_type AS (post_id bigint, headline text, rank real, rank_cd real);

CREATE OR REPLACE FUNCTION find_post(text)
	RETURNS SETOF find_post_type AS
$body$
	SELECT tsearch2.set_curcfg('default');
	
	SELECT post_id, tsearch2.headline(post_excerpt_xhtml || post_content_xhtml,q),
	tsearch2.rank(post_idx,q) as rank, tsearch2.rank_cd(post_idx,q) as rank_cd
	FROM dc_post, tsearch2.to_tsquery($1) AS q
	WHERE tsearch2.exectsq(post_idx,q);
$body$
LANGUAGE sql;

--
-- Find comment_id with query
--
--DROP TYPE find_comment_type CASCADE;
CREATE TYPE find_comment_type AS (comment_id bigint, headline text, rank real, rank_cd real);

CREATE OR REPLACE FUNCTION find_comment(text)
	RETURNS SETOF find_comment_type AS
$body$
	SELECT tsearch2.set_curcfg('default');
	
	SELECT comment_id, tsearch2.headline(comment_content,q),
	tsearch2.rank(comment_idx,q) as rank, tsearch2.rank_cd(comment_idx,q) as rank_cd
	FROM dc_comment, tsearch2.to_tsquery($1) AS q
	WHERE tsearch2.exectsq(comment_idx,q);
$body$
LANGUAGE sql;


/* TRIGGERS
======================================================== */
CREATE OR REPLACE FUNCTION trigger_post_idx()
	RETURNS "trigger" AS
$body$
DECLARE
	v_rec RECORD;
BEGIN
	IF TG_OP = 'INSERT' THEN
		IF NEW.post_idx IS NOT NULL THEN
			RETURN NEW;
		END IF;
		
		IF NEW.post_content_xhtml IS NULL AND NEW.post_excerpt_xhtml IS NULL AND NEW.post_title IS NULL THEN
			RETURN NEW;
		END IF;
	END IF;
	
	IF NEW.post_content_xhtml IS NULL OR NEW.post_excerpt_xhtml IS NULL OR NEW.post_title IS NULL THEN
		SELECT post_title, post_content_xhtml, post_excerpt_xhtml INTO v_rec
		FROM dc_post
		WHERE post_id = OLD.post_id;
		
		IF NEW.post_content_xhtml IS NULL THEN
			NEW.post_content_xhtml := v_rec.post_content_xhtml;
		END IF;
		
		IF NEW.post_excerpt_xhtml IS NULL THEN
			NEW.post_excerpt_xhtml := v_rec.post_excerpt_xhtml;
		END IF;
		
		IF NEW.post_title IS NULL THEN
			NEW.post_title := v_rec.post_title;
		END IF;
	END IF;
	
	IF NEW.post_content_xhtml IS NOT NULL OR NEW.post_excerpt_xhtml IS NOT NULL OR NEW.post_title IS NOT NULL THEN
		NEW.post_idx = get_vectors(NEW.post_title,NEW.post_excerpt_xhtml || NEW.post_content_xhtml);
		RETURN NEW;
	END IF;
	
	RETURN NULL;
END;
$body$
LANGUAGE plpgsql;

CREATE TRIGGER trg_dc_post_idx BEFORE INSERT OR UPDATE ON dc_post
FOR EACH ROW EXECUTE PROCEDURE trigger_post_idx();


CREATE OR REPLACE FUNCTION trigger_comment_idx()
	RETURNS "trigger" AS
$body$
DECLARE
	v_rec RECORD;
BEGIN
	IF TG_OP = 'INSERT' THEN
		IF NEW.comment_idx IS NOT NULL THEN
			RETURN NEW;
		END IF;
		
		IF NEW.comment_content IS NULL THEN
			RETURN NEW;
		END IF;
	END IF;
	
	IF NEW.comment_content IS NULL THEN
		SELECT comment_content INTO v_rec
		FROM dc_comment
		WHERE comment_id = OLD.comment_id;
		
		NEW.comment_content := v_rec.comment_content;
	END IF;
	
	IF NEW.comment_content IS NOT NULL THEN
		NEW.comment_idx = get_vectors('',NEW.comment_content);
		RETURN NEW;
	END IF;
	
	RETURN NULL;
END;
$body$
LANGUAGE plpgsql;

CREATE TRIGGER trg_dc_comment_idx BEFORE INSERT OR UPDATE ON dc_comment
FOR EACH ROW EXECUTE PROCEDURE trigger_comment_idx();

/* INDEXING ALL POSTS AND COMMENTS
======================================================== */
UPDATE dc_post SET post_idx = get_vectors(post_title,post_excerpt_xhtml || post_content_xhtml);
UPDATE dc_comment SET comment_idx = get_vectors('',comment_content);

END;