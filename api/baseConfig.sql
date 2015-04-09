--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = ON;
SET check_function_bodies = FALSE;
SET client_min_messages = WARNING;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = PUBLIC, pg_catalog;

--
-- Name: operation; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE operation AS ENUM (
  'SELECT',
  'INSERT',
  'DELETE',
  'UPDATE'
);


ALTER TYPE public.operation
OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = FALSE;

--
-- Name: dataTypes; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE "dataTypes" (
  id          BIGINT NOT NULL,
  name        NAME,
  description TEXT
);


ALTER TABLE public."dataTypes" OWNER TO postgres;

--
-- Name: logs; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE logs (
  id           BIGINT NOT NULL,
  "userId"     BIGINT,
  "timestamp"  TIMESTAMP WITHOUT TIME ZONE,
  "dataTypeId" BIGINT,
  "dataId"     BIGINT,
  operation    operation,
  description  TEXT
);


ALTER TABLE public.logs OWNER TO postgres;

--
-- Name: markups; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE markups (
  id           BIGINT NOT NULL,
  "userId"     BIGINT,
  "dataTypeId" BIGINT,
  "dataId"     BIGINT,
  name         NAME,
  value        TEXT
);


ALTER TABLE public.markups OWNER TO postgres;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE permissions (
  id                 BIGINT NOT NULL,
  name               NAME,
  operation          operation,
  "dataTypeId"       BIGINT,
  "attributeBag"     NAME [],
  "regionExpression" JSON
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE roles (
  id          BIGINT NOT NULL,
  name        NAME,
  description TEXT
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: rolePermissionMaps; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE "rolePermissionMaps" (
  id             BIGINT NOT NULL,
  "roleId"       BIGINT,
  "permissionId" BIGINT
);


ALTER TABLE public."rolePermissionMaps" OWNER TO postgres;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE sessions (
  id                  BIGINT NOT NULL,
  "sessionId"         NAME,
  "userId"            BIGINT,
  "startTime"         TIMESTAMP WITHOUT TIME ZONE,
  "appendInfo"        JSON,
  "lastOperationTime" TIMESTAMP WITHOUT TIME ZONE
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE "users" (
  id          BIGINT NOT NULL,
  no          NAME,
  name        NAME,
  alias       NAME,
  photo       CHARACTER VARYING(128),
  password    CHARACTER VARYING(64),
  login       NAME,
  description TEXT
);


ALTER TABLE public."users" OWNER TO postgres;

--
-- Name: userPermissionMaps; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE "userPermissionMaps" (
  id                BIGINT NOT NULL,
  "userId"          BIGINT,
  "permissionId"    BIGINT,
  "regionArguments" JSON
);


ALTER TABLE public."userPermissionMaps" OWNER TO postgres;

--
-- Name: userRoleMaps; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE "userRoleMaps" (
  id                BIGINT NOT NULL,
  "userId"          BIGINT,
  "roleId"          BIGINT,
  "regionArguments" JSON []
);


ALTER TABLE public."userRoleMaps" OWNER TO postgres;


--
-- Data for Name: dataTypes; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO "dataTypes" ("id", "name", "description") VALUES
  (1, 'dataTypes', 'permission system operation object'),
  (2, 'logs', 'log system'),
  (3, 'markups', 'tag system'),
  (4, 'permissions', 'permission base abstraction'),
  (5, 'roles', 'set of permission'),
  (6, 'rolePermissionMaps', 'role permission map'),
  (7, 'sessions', 'user session info'),
  (8, 'users', 'permission system subject'),
  (9, 'userPermissionMaps', 'user permission map'),
  (10, 'userRoleMaps', 'user role map');


--
-- Data for Name: logs; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: markups; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO "permissions" ("id", "name", operation, "dataTypeId", "attributeBag", "regionExpression") VALUES
  (1, 'superDataTypesRead', 'SELECT', 1, ARRAY['id', 'name', 'description'], NULL),
  (2, 'superDataTypesCreate', 'INSERT', 1, ARRAY['id', 'name', 'description'], NULL),
  (3, 'superDataTypesUpdate', 'UPDATE', 1, ARRAY['id', 'name', 'description'], NULL),
  (4, 'superDataTypesDelete', 'DELETE', 1, ARRAY['id', 'name', 'description'], NULL),
  (5, 'superLogsRead', 'SELECT', 2, ARRAY['id', 'userId', 'timestamp', 'dataTypeId', 'dataId', 'operation', 'description'], NULL),
  (6, 'superLogsCreate', 'INSERT', 2, ARRAY['id', 'userId', 'timestamp', 'dataTypeId', 'dataId', 'operation', 'description'], NULL),
  (7, 'superLogsUpdate', 'UPDATE', 2, ARRAY['id', 'userId', 'timestamp', 'dataTypeId', 'dataId', 'operation', 'description'], NULL),
  (8, 'superLogsDelete', 'DELETE', 2, ARRAY['id', 'userId', 'timestamp', 'dataTypeId', 'dataId', 'operation', 'description'], NULL),
  (9, 'superMarkupsRead', 'SELECT', 3, ARRAY['id', 'userId', 'dataTypeId', 'dataId', 'name', 'value'], NULL),
  (10, 'superMarkupsCreate', 'INSERT', 3, ARRAY['id', 'userId', 'dataTypeId', 'dataId', 'name', 'value'], NULL),
  (11, 'superMarkupsUpdate', 'UPDATE', 3, ARRAY['id', 'userId', 'dataTypeId', 'dataId', 'name', 'value'], NULL),
  (12, 'superMarkupsDelete', 'DELETE', 3, ARRAY['id', 'userId', 'dataTypeId', 'dataId', 'name', 'value'], NULL),
  (13, 'superPermissionsRead', 'SELECT', 4, ARRAY['id', 'name', 'operation', 'dataTypeId', 'attributeBug', 'regionExpression'], NULL),
  (14, 'superPermissionsCreate', 'INSERT', 4, ARRAY['id', 'name', 'operation', 'dataTypeId', 'attributeBug', 'regionExpression'], NULL),
  (15, 'superPermissionsUpdate', 'UPDATE', 4, ARRAY['id', 'name', 'operation', 'dataTypeId', 'attributeBug', 'regionExpression'], NULL),
  (16, 'superPermissionsDelete', 'DELETE', 4, ARRAY['id', 'name', 'operation', 'dataTypeId', 'attributeBug', 'regionExpression'], NULL),
  (17, 'superRolesRead', 'SELECT', 5, ARRAY['id', 'name', 'description'], NULL),
  (18, 'superRolesCreate', 'INSERT', 5, ARRAY['id', 'name', 'description'], NULL),
  (19, 'superRolesUpdate', 'UPDATE', 5, ARRAY['id', 'name', 'description'], NULL),
  (20, 'superRolesDelete', 'DELETE', 5, ARRAY['id', 'name', 'description'], NULL),
  (21, 'superRolePermissionMapsRead', 'SELECT', 6, ARRAY['id', 'roleId', 'permissionId'], NULL),
  (22, 'superRolePermissionMapsCreate', 'INSERT', 6, ARRAY['id', 'roleId', 'permissionId'], NULL),
  (23, 'superRolePermissionMapsUpdate', 'UPDATE', 6, ARRAY['id', 'roleId', 'permissionId'], NULL),
  (24, 'superRolePermissionMapsDelete', 'DELETE', 6, ARRAY['id', 'roleId', 'permissionId'], NULL),
  (25, 'superSessionsRead', 'SELECT', 7, ARRAY['id', 'sessionId', 'userId', 'startTime', 'appendInfo', 'lastOperationTime'], NULL),
  (26, 'superSessionsCreate', 'INSERT', 7, ARRAY['id', 'sessionId', 'userId', 'startTime', 'appendInfo', 'lastOperationTime'], NULL),
  (27, 'superSessionsUpdate', 'UPDATE', 7, ARRAY['id', 'sessionId', 'userId', 'startTime', 'appendInfo', 'lastOperationTime'], NULL),
  (28, 'superSessionsDelete', 'DELETE', 7, ARRAY['id', 'sessionId', 'userId', 'startTime', 'appendInfo', 'lastOperationTime'], NULL),
  (29, 'superUsersRead', 'SELECT', 8, ARRAY['id', 'no', 'name', 'alias', 'photo', 'login', 'password', 'description'], NULL),
  (30, 'superUsersCreate', 'INSERT', 8, ARRAY['id', 'no', 'name', 'alias', 'photo', 'login', 'password', 'description'], NULL),
  (31, 'superUsersUpdate', 'UPDATE', 8, ARRAY['id', 'no', 'name', 'alias', 'photo', 'login', 'password', 'description'], NULL),
  (32, 'superUsersDelete', 'DELETE', 8, ARRAY['id', 'no', 'name', 'alias', 'photo', 'login', 'password', 'description'], NULL),
  (33, 'superUserPermissionMapsRead', 'SELECT', 9, ARRAY['id', 'userId', 'permissionId', 'regionArguments'], NULL),
  (34, 'superUserPermissionMapsCreate', 'INSERT', 9, ARRAY['id', 'userId', 'permissionId', 'regionArguments'], NULL),
  (35, 'superUserPermissionMapsUpdate', 'UPDATE', 9, ARRAY['id', 'userId', 'permissionId', 'regionArguments'], NULL),
  (36, 'superUserPermissionMapsDelete', 'DELETE', 9, ARRAY['id', 'userId', 'permissionId', 'regionArguments'], NULL),
  (37, 'superUserRoleMapsRead', 'SELECT', 10, ARRAY['id', 'userId', 'roleId', 'regionArguments'], NULL),
  (38, 'superUserRoleMapsCreate', 'INSERT', 10, ARRAY['id', 'userId', 'roleId', 'regionArguments'], NULL),
  (39, 'superUserRoleMapsUpdate', 'UPDATE', 10, ARRAY['id', 'userId', 'roleId', 'regionArguments'], NULL),
  (40, 'superUserRoleMapsDelete', 'DELETE', 10, ARRAY['id', 'userId', 'roleId', 'regionArguments'], NULL);


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO roles ("id", "name", "description") VALUES
  (1, 'root', 'supervisor');

--
-- Data for Name: rolePermissionMaps; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO rolePermissionMaps ("id", "roleId", "permissionId") VALUES
  (1, 1, 1), (1, 1, 2), (1, 1, 3), (1, 1, 4),
  (1, 1, 5), (1, 1, 6), (1, 1, 7), (1, 1, 8),
  (1, 1, 9), (1, 1, 10), (1, 1, 11), (1, 1, 12),
  (1, 1, 13), (1, 1, 14), (1, 1, 15), (1, 1, 16),
  (1, 1, 17), (1, 1, 18), (1, 1, 19), (1, 1, 20),
  (1, 1, 21), (1, 1, 22), (1, 1, 23), (1, 1, 24),
  (1, 1, 25), (1, 1, 26), (1, 1, 27), (1, 1, 28),
  (1, 1, 29), (1, 1, 30), (1, 1, 31), (1, 1, 32),
  (1, 1, 33), (1, 1, 34), (1, 1, 35), (1, 1, 36),
  (1, 1, 37), (1, 1, 38), (1, 1, 39), (1, 1, 40);

--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO users ("id", "no", "name", "alias", "photo", "login", "password", "description") VALUES
  (1, 'admin', 'admin', 'admin', '', 'admin', 'admin', 'administrator');


--
-- Data for Name: userPermissionMaps; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: userRoleMaps; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO "userRoleMaps" ("id", "userId", "roleId", "regionArguments") VALUES
  (1, 1, 1, NULL);

--
-- Name: dataType_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY "dataTypes"
ADD CONSTRAINT "dataType_pk" PRIMARY KEY (id);


--
-- Name: log_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY logs
ADD CONSTRAINT log_pk PRIMARY KEY (id);


--
-- Name: markup_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY markups
ADD CONSTRAINT markup_pk PRIMARY KEY (id);


--
-- Name: permission_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY permissions
ADD CONSTRAINT permission_pk PRIMARY KEY (id);


--
-- Name: rolePermissionMap_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY "rolePermissionMaps"
ADD CONSTRAINT "rolePermissionMap_pk" PRIMARY KEY (id);


--
-- Name: role_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY roles
ADD CONSTRAINT role_pk PRIMARY KEY (id);


--
-- Name: session_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY sessions
ADD CONSTRAINT session_pk PRIMARY KEY (id);


--
-- Name: session_unique; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY sessions
ADD CONSTRAINT session_unique UNIQUE ("sessionId");


--
-- Name: userPermissionMap_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY "userPermissionMaps"
ADD CONSTRAINT "userPermissionMap_pk" PRIMARY KEY (id);


--
-- Name: userRoleMap_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY "userRoleMaps"
ADD CONSTRAINT "userRoleMap_pk" PRIMARY KEY (id);


--
-- Name: user_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY "users"
ADD CONSTRAINT user_pk PRIMARY KEY (id);


--
-- Name: fki_markup_dataType_fk; Type: INDEX; Schema: public; Owner: postgres; Tablespace:
--

CREATE INDEX "fki_markup_dataType_fk" ON markups USING BTREE ("dataTypeId");


--
-- Name: fki_permission_dataType_fk; Type: INDEX; Schema: public; Owner: postgres; Tablespace:
--

CREATE INDEX "fki_permission_dataType_fk" ON permissions USING BTREE ("dataTypeId");


--
-- Name: log_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY logs
ADD CONSTRAINT log_user_fk FOREIGN KEY ("userId") REFERENCES "users" (id);


--
-- Name: markup_dataType_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY markups
ADD CONSTRAINT "markup_dataType_fk" FOREIGN KEY ("dataTypeId") REFERENCES "dataTypes" (id);


--
-- Name: markup_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY markups
ADD CONSTRAINT markup_user_fk FOREIGN KEY ("userId") REFERENCES "users" (id);


--
-- Name: permission_dataType_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY permissions
ADD CONSTRAINT "permission_dataType_fk" FOREIGN KEY ("dataTypeId") REFERENCES "dataTypes" (id);


--
-- Name: rolePermissionMap_permission_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "rolePermissionMaps"
ADD CONSTRAINT "rolePermissionMap_permission_fk" FOREIGN KEY ("permissionId") REFERENCES permissions (id);


--
-- Name: rolePermissionMap_role_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "rolePermissionMaps"
ADD CONSTRAINT "rolePermissionMap_role_fk" FOREIGN KEY ("roleId") REFERENCES roles (id);


--
-- Name: session_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY sessions
ADD CONSTRAINT session_user_fk FOREIGN KEY ("userId") REFERENCES "users" (id);


--
-- Name: userPermissionMap_permission_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "userPermissionMaps"
ADD CONSTRAINT "userPermissionMap_permission_fk" FOREIGN KEY ("permissionId") REFERENCES permissions (id);


--
-- Name: userPermissionMap_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "userPermissionMaps"
ADD CONSTRAINT "userPermissionMap_user_fk" FOREIGN KEY ("userId") REFERENCES "users" (id);


--
-- Name: userRoleMap_role_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "userRoleMaps"
ADD CONSTRAINT "userRoleMap_role_fk" FOREIGN KEY ("roleId") REFERENCES roles (id);


--
-- Name: userRoleMap_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "userRoleMaps"
ADD CONSTRAINT "userRoleMap_user_fk" FOREIGN KEY ("userId") REFERENCES "users" (id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

