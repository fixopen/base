--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: operation; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE operation AS ENUM (
    'SELECT',
    'INSERT',
    'DELETE',
    'UPDATE'
);


ALTER TYPE public.operation OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: dataTypes; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "dataTypes" (
    id bigint NOT NULL,
    name name,
    description text
);


ALTER TABLE public."dataTypes" OWNER TO postgres;

--
-- Name: logs; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE logs (
    id bigint NOT NULL,
    "userId" bigint,
    "timestamp" timestamp without time zone,
    "dataTypeId" bigint,
    "dataId" bigint,
    operation operation,
    description text
);


ALTER TABLE public.logs OWNER TO postgres;

--
-- Name: markups; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE markups (
    id bigint NOT NULL,
    "userId" bigint,
    "dataTypeId" bigint,
    "dataId" bigint,
    name name,
    value text
);


ALTER TABLE public.markups OWNER TO postgres;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE permissions (
    id bigint NOT NULL,
    name name,
    operation operation,
    "dataTypeId" bigint,
    "attributeBag" name[],
    "regionExpression" json
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- Name: rolePermissionMaps; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "rolePermissionMaps" (
    id bigint NOT NULL,
    "roleId" bigint,
    "permissionId" bigint
);


ALTER TABLE public."rolePermissionMaps" OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE roles (
    id bigint NOT NULL,
    name name,
    description text
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE sessions (
    id bigint NOT NULL,
    "sessionId" name,
    "userId" bigint,
    "startTime" timestamp without time zone,
    "appendInfo" json,
    "lastOperationTime" timestamp without time zone
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: userPermissionMaps; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "userPermissionMaps" (
    id bigint NOT NULL,
    "userId" bigint,
    "permissionId" bigint,
    "regionArguments" json
);


ALTER TABLE public."userPermissionMaps" OWNER TO postgres;

--
-- Name: userRoleMaps; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "userRoleMaps" (
    id bigint NOT NULL,
    "userId" bigint,
    "roleId" bigint,
    "regionArguments" json[]
);


ALTER TABLE public."userRoleMaps" OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE users (
    id bigint NOT NULL,
    no name,
    name name,
    alias name,
    photo character varying(128),
    password character varying(64),
    login name,
    description text
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Data for Name: dataTypes; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO "dataTypes" VALUES (1, 'dataTypes', 'permission system operation object');
INSERT INTO "dataTypes" VALUES (2, 'logs', 'log system');
INSERT INTO "dataTypes" VALUES (3, 'markups', 'tag system');
INSERT INTO "dataTypes" VALUES (4, 'permissions', 'permission base abstraction');
INSERT INTO "dataTypes" VALUES (5, 'roles', 'set of permission');
INSERT INTO "dataTypes" VALUES (6, 'rolePermissionMaps', 'role permission map');
INSERT INTO "dataTypes" VALUES (7, 'sessions', 'user session info');
INSERT INTO "dataTypes" VALUES (8, 'users', 'permission system subject');
INSERT INTO "dataTypes" VALUES (9, 'userPermissionMaps', 'user permission map');
INSERT INTO "dataTypes" VALUES (10, 'userRoleMaps', 'user role map');


--
-- Data for Name: logs; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: markups; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO permissions VALUES (1, 'superDataTypesRead', 'SELECT', 1, '{id,name,description}', NULL);
INSERT INTO permissions VALUES (2, 'superDataTypesCreate', 'INSERT', 1, '{id,name,description}', NULL);
INSERT INTO permissions VALUES (3, 'superDataTypesUpdate', 'UPDATE', 1, '{id,name,description}', NULL);
INSERT INTO permissions VALUES (4, 'superDataTypesDelete', 'DELETE', 1, '{id,name,description}', NULL);
INSERT INTO permissions VALUES (5, 'superLogsRead', 'SELECT', 2, '{id,userId,timestamp,dataTypeId,dataId,operation,description}', NULL);
INSERT INTO permissions VALUES (6, 'superLogsCreate', 'INSERT', 2, '{id,userId,timestamp,dataTypeId,dataId,operation,description}', NULL);
INSERT INTO permissions VALUES (7, 'superLogsUpdate', 'UPDATE', 2, '{id,userId,timestamp,dataTypeId,dataId,operation,description}', NULL);
INSERT INTO permissions VALUES (8, 'superLogsDelete', 'DELETE', 2, '{id,userId,timestamp,dataTypeId,dataId,operation,description}', NULL);
INSERT INTO permissions VALUES (9, 'superMarkupsRead', 'SELECT', 3, '{id,userId,dataTypeId,dataId,name,value}', NULL);
INSERT INTO permissions VALUES (10, 'superMarkupsCreate', 'INSERT', 3, '{id,userId,dataTypeId,dataId,name,value}', NULL);
INSERT INTO permissions VALUES (11, 'superMarkupsUpdate', 'UPDATE', 3, '{id,userId,dataTypeId,dataId,name,value}', NULL);
INSERT INTO permissions VALUES (12, 'superMarkupsDelete', 'DELETE', 3, '{id,userId,dataTypeId,dataId,name,value}', NULL);
INSERT INTO permissions VALUES (13, 'superPermissionsRead', 'SELECT', 4, '{id,name,operation,dataTypeId,attributeBug,regionExpression}', NULL);
INSERT INTO permissions VALUES (14, 'superPermissionsCreate', 'INSERT', 4, '{id,name,operation,dataTypeId,attributeBug,regionExpression}', NULL);
INSERT INTO permissions VALUES (15, 'superPermissionsUpdate', 'UPDATE', 4, '{id,name,operation,dataTypeId,attributeBug,regionExpression}', NULL);
INSERT INTO permissions VALUES (16, 'superPermissionsDelete', 'DELETE', 4, '{id,name,operation,dataTypeId,attributeBug,regionExpression}', NULL);
INSERT INTO permissions VALUES (17, 'superRolesRead', 'SELECT', 5, '{id,name,description}', NULL);
INSERT INTO permissions VALUES (18, 'superRolesCreate', 'INSERT', 5, '{id,name,description}', NULL);
INSERT INTO permissions VALUES (19, 'superRolesUpdate', 'UPDATE', 5, '{id,name,description}', NULL);
INSERT INTO permissions VALUES (20, 'superRolesDelete', 'DELETE', 5, '{id,name,description}', NULL);
INSERT INTO permissions VALUES (21, 'superRolePermissionMapsRead', 'SELECT', 6, '{id,roleId,permissionId}', NULL);
INSERT INTO permissions VALUES (22, 'superRolePermissionMapsCreate', 'INSERT', 6, '{id,roleId,permissionId}', NULL);
INSERT INTO permissions VALUES (23, 'superRolePermissionMapsUpdate', 'UPDATE', 6, '{id,roleId,permissionId}', NULL);
INSERT INTO permissions VALUES (24, 'superRolePermissionMapsDelete', 'DELETE', 6, '{id,roleId,permissionId}', NULL);
INSERT INTO permissions VALUES (25, 'superSessionsRead', 'SELECT', 7, '{id,sessionId,userId,startTime,appendInfo,lastOperationTime}', NULL);
INSERT INTO permissions VALUES (26, 'superSessionsCreate', 'INSERT', 7, '{id,sessionId,userId,startTime,appendInfo,lastOperationTime}', NULL);
INSERT INTO permissions VALUES (27, 'superSessionsUpdate', 'UPDATE', 7, '{id,sessionId,userId,startTime,appendInfo,lastOperationTime}', NULL);
INSERT INTO permissions VALUES (28, 'superSessionsDelete', 'DELETE', 7, '{id,sessionId,userId,startTime,appendInfo,lastOperationTime}', NULL);
INSERT INTO permissions VALUES (29, 'superUsersRead', 'SELECT', 8, '{id,no,name,alias,photo,login,password,description}', NULL);
INSERT INTO permissions VALUES (30, 'superUsersCreate', 'INSERT', 8, '{id,no,name,alias,photo,login,password,description}', NULL);
INSERT INTO permissions VALUES (31, 'superUsersUpdate', 'UPDATE', 8, '{id,no,name,alias,photo,login,password,description}', NULL);
INSERT INTO permissions VALUES (32, 'superUsersDelete', 'DELETE', 8, '{id,no,name,alias,photo,login,password,description}', NULL);
INSERT INTO permissions VALUES (33, 'superUserPermissionMapsRead', 'SELECT', 9, '{id,userId,permissionId,regionArguments}', NULL);
INSERT INTO permissions VALUES (34, 'superUserPermissionMapsCreate', 'INSERT', 9, '{id,userId,permissionId,regionArguments}', NULL);
INSERT INTO permissions VALUES (35, 'superUserPermissionMapsUpdate', 'UPDATE', 9, '{id,userId,permissionId,regionArguments}', NULL);
INSERT INTO permissions VALUES (36, 'superUserPermissionMapsDelete', 'DELETE', 9, '{id,userId,permissionId,regionArguments}', NULL);
INSERT INTO permissions VALUES (37, 'superUserRoleMapsRead', 'SELECT', 10, '{id,userId,roleId,regionArguments}', NULL);
INSERT INTO permissions VALUES (38, 'superUserRoleMapsCreate', 'INSERT', 10, '{id,userId,roleId,regionArguments}', NULL);
INSERT INTO permissions VALUES (39, 'superUserRoleMapsUpdate', 'UPDATE', 10, '{id,userId,roleId,regionArguments}', NULL);
INSERT INTO permissions VALUES (40, 'superUserRoleMapsDelete', 'DELETE', 10, '{id,userId,roleId,regionArguments}', NULL);


--
-- Data for Name: rolePermissionMaps; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO "rolePermissionMaps" VALUES (1, 1, 1);
INSERT INTO "rolePermissionMaps" VALUES (2, 1, 2);
INSERT INTO "rolePermissionMaps" VALUES (3, 1, 3);
INSERT INTO "rolePermissionMaps" VALUES (4, 1, 4);
INSERT INTO "rolePermissionMaps" VALUES (5, 1, 5);
INSERT INTO "rolePermissionMaps" VALUES (6, 1, 6);
INSERT INTO "rolePermissionMaps" VALUES (7, 1, 7);
INSERT INTO "rolePermissionMaps" VALUES (8, 1, 8);
INSERT INTO "rolePermissionMaps" VALUES (9, 1, 9);
INSERT INTO "rolePermissionMaps" VALUES (10, 1, 10);
INSERT INTO "rolePermissionMaps" VALUES (11, 1, 11);
INSERT INTO "rolePermissionMaps" VALUES (12, 1, 12);
INSERT INTO "rolePermissionMaps" VALUES (13, 1, 13);
INSERT INTO "rolePermissionMaps" VALUES (14, 1, 14);
INSERT INTO "rolePermissionMaps" VALUES (15, 1, 15);
INSERT INTO "rolePermissionMaps" VALUES (16, 1, 16);
INSERT INTO "rolePermissionMaps" VALUES (17, 1, 17);
INSERT INTO "rolePermissionMaps" VALUES (18, 1, 18);
INSERT INTO "rolePermissionMaps" VALUES (19, 1, 19);
INSERT INTO "rolePermissionMaps" VALUES (20, 1, 20);
INSERT INTO "rolePermissionMaps" VALUES (21, 1, 21);
INSERT INTO "rolePermissionMaps" VALUES (22, 1, 22);
INSERT INTO "rolePermissionMaps" VALUES (23, 1, 23);
INSERT INTO "rolePermissionMaps" VALUES (24, 1, 24);
INSERT INTO "rolePermissionMaps" VALUES (25, 1, 25);
INSERT INTO "rolePermissionMaps" VALUES (26, 1, 26);
INSERT INTO "rolePermissionMaps" VALUES (27, 1, 27);
INSERT INTO "rolePermissionMaps" VALUES (28, 1, 28);
INSERT INTO "rolePermissionMaps" VALUES (29, 1, 29);
INSERT INTO "rolePermissionMaps" VALUES (30, 1, 30);
INSERT INTO "rolePermissionMaps" VALUES (31, 1, 31);
INSERT INTO "rolePermissionMaps" VALUES (32, 1, 32);
INSERT INTO "rolePermissionMaps" VALUES (33, 1, 33);
INSERT INTO "rolePermissionMaps" VALUES (34, 1, 34);
INSERT INTO "rolePermissionMaps" VALUES (35, 1, 35);
INSERT INTO "rolePermissionMaps" VALUES (36, 1, 36);
INSERT INTO "rolePermissionMaps" VALUES (37, 1, 37);
INSERT INTO "rolePermissionMaps" VALUES (38, 1, 38);
INSERT INTO "rolePermissionMaps" VALUES (39, 1, 39);
INSERT INTO "rolePermissionMaps" VALUES (40, 1, 40);


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO roles VALUES (1, 'root', 'supervisor');


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: userPermissionMaps; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: userRoleMaps; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO "userRoleMaps" VALUES (1, 1, 1, NULL);


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO users VALUES (1, 'admin', 'admin', 'admin', '', 'admin', 'admin', 'administrator');


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

ALTER TABLE ONLY users
    ADD CONSTRAINT user_pk PRIMARY KEY (id);


--
-- Name: fki_markup_dataType_fk; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX "fki_markup_dataType_fk" ON markups USING btree ("dataTypeId");


--
-- Name: fki_permission_dataType_fk; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX "fki_permission_dataType_fk" ON permissions USING btree ("dataTypeId");


--
-- Name: log_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY logs
    ADD CONSTRAINT log_user_fk FOREIGN KEY ("userId") REFERENCES users(id);


--
-- Name: markup_dataType_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY markups
    ADD CONSTRAINT "markup_dataType_fk" FOREIGN KEY ("dataTypeId") REFERENCES "dataTypes"(id);


--
-- Name: markup_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY markups
    ADD CONSTRAINT markup_user_fk FOREIGN KEY ("userId") REFERENCES users(id);


--
-- Name: permission_dataType_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT "permission_dataType_fk" FOREIGN KEY ("dataTypeId") REFERENCES "dataTypes"(id);


--
-- Name: rolePermissionMap_permission_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "rolePermissionMaps"
    ADD CONSTRAINT "rolePermissionMap_permission_fk" FOREIGN KEY ("permissionId") REFERENCES permissions(id);


--
-- Name: rolePermissionMap_role_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "rolePermissionMaps"
    ADD CONSTRAINT "rolePermissionMap_role_fk" FOREIGN KEY ("roleId") REFERENCES roles(id);


--
-- Name: session_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY sessions
    ADD CONSTRAINT session_user_fk FOREIGN KEY ("userId") REFERENCES users(id);


--
-- Name: userPermissionMap_permission_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "userPermissionMaps"
    ADD CONSTRAINT "userPermissionMap_permission_fk" FOREIGN KEY ("permissionId") REFERENCES permissions(id);


--
-- Name: userPermissionMap_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "userPermissionMaps"
    ADD CONSTRAINT "userPermissionMap_user_fk" FOREIGN KEY ("userId") REFERENCES users(id);


--
-- Name: userRoleMap_role_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "userRoleMaps"
    ADD CONSTRAINT "userRoleMap_role_fk" FOREIGN KEY ("roleId") REFERENCES roles(id);


--
-- Name: userRoleMap_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "userRoleMaps"
    ADD CONSTRAINT "userRoleMap_user_fk" FOREIGN KEY ("userId") REFERENCES users(id);


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

