--
-- PostgreSQL database dump
--

\restrict 5v6Jx7faChPIiZDYVQvOmBk1bailGmSPGYmCkJz9AhQZWTYnfK9uZYfsoWnjmzY

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: agencies; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.agencies (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    phone character varying(50),
    country character(2),
    currency_code character(3) DEFAULT 'AZN'::bpchar NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.agencies OWNER TO sail;

--
-- Name: agencies_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.agencies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agencies_id_seq OWNER TO sail;

--
-- Name: agencies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.agencies_id_seq OWNED BY public.agencies.id;


--
-- Name: agency_users; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.agency_users (
    id bigint NOT NULL,
    agency_id bigint NOT NULL,
    user_id bigint NOT NULL,
    role character varying(255) DEFAULT 'staff'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT agency_users_role_check CHECK (((role)::text = ANY ((ARRAY['owner'::character varying, 'manager'::character varying, 'staff'::character varying])::text[])))
);


ALTER TABLE public.agency_users OWNER TO sail;

--
-- Name: agency_users_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.agency_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.agency_users_id_seq OWNER TO sail;

--
-- Name: agency_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.agency_users_id_seq OWNED BY public.agency_users.id;


--
-- Name: attachments; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.attachments (
    id bigint NOT NULL,
    attachable_type character varying(255),
    attachable_id bigint,
    uploader_id bigint NOT NULL,
    disk character varying(255) DEFAULT 'local'::character varying NOT NULL,
    path character varying(255) NOT NULL,
    filename character varying(255) NOT NULL,
    mime_type character varying(255),
    size bigint DEFAULT '0'::bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.attachments OWNER TO sail;

--
-- Name: attachments_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.attachments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.attachments_id_seq OWNER TO sail;

--
-- Name: attachments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.attachments_id_seq OWNED BY public.attachments.id;


--
-- Name: bookings; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.bookings (
    id bigint NOT NULL,
    proposal_id bigint NOT NULL,
    request_id bigint NOT NULL,
    agency_id bigint NOT NULL,
    operator_id bigint NOT NULL,
    confirmed_at timestamp(0) without time zone NOT NULL,
    travel_date_from date NOT NULL,
    travel_date_to date NOT NULL,
    pax_count integer NOT NULL,
    final_price numeric(10,2) NOT NULL,
    currency character varying(3) NOT NULL,
    status character varying(255) DEFAULT 'confirmed'::character varying NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bookings OWNER TO sail;

--
-- Name: bookings_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.bookings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bookings_id_seq OWNER TO sail;

--
-- Name: bookings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.bookings_id_seq OWNED BY public.bookings.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO sail;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO sail;

--
-- Name: clients; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.clients (
    id bigint NOT NULL,
    agency_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    phone character varying(255),
    passport_number character varying(255),
    nationality character(2),
    date_of_birth date,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.clients OWNER TO sail;

--
-- Name: clients_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.clients_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clients_id_seq OWNER TO sail;

--
-- Name: clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.clients_id_seq OWNED BY public.clients.id;


--
-- Name: currencies; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.currencies (
    code character(3) NOT NULL,
    name character varying(60) NOT NULL,
    rate numeric(12,6) DEFAULT '1'::numeric NOT NULL,
    is_active boolean DEFAULT false NOT NULL,
    is_default boolean DEFAULT false NOT NULL,
    rates_updated_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.currencies OWNER TO sail;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO sail;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO sail;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO sail;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO sail;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO sail;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: markup_settings; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.markup_settings (
    id bigint NOT NULL,
    service_type character varying(255) NOT NULL,
    markup_pct numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.markup_settings OWNER TO sail;

--
-- Name: markup_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.markup_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.markup_settings_id_seq OWNER TO sail;

--
-- Name: markup_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.markup_settings_id_seq OWNED BY public.markup_settings.id;


--
-- Name: media; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.media (
    id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL,
    uuid uuid,
    collection_name character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    file_name character varying(255) NOT NULL,
    mime_type character varying(255),
    disk character varying(255) NOT NULL,
    conversions_disk character varying(255),
    size bigint NOT NULL,
    manipulations json NOT NULL,
    custom_properties json NOT NULL,
    generated_conversions json NOT NULL,
    responsive_images json NOT NULL,
    order_column integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.media OWNER TO sail;

--
-- Name: media_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.media_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.media_id_seq OWNER TO sail;

--
-- Name: media_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.media_id_seq OWNED BY public.media.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO sail;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO sail;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: offer_items; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.offer_items (
    id bigint NOT NULL,
    offer_id bigint NOT NULL,
    supplier_service_id bigint,
    type character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    quantity smallint DEFAULT '1'::smallint NOT NULL,
    unit_price numeric(10,2) NOT NULL,
    currency character(3) NOT NULL,
    price_unit character varying(255) DEFAULT 'per_person'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    unit_price_azn numeric(12,2),
    exchange_rate numeric(12,6)
);


ALTER TABLE public.offer_items OWNER TO sail;

--
-- Name: offer_items_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.offer_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.offer_items_id_seq OWNER TO sail;

--
-- Name: offer_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.offer_items_id_seq OWNED BY public.offer_items.id;


--
-- Name: offers; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.offers (
    id bigint NOT NULL,
    rfq_id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    is_partial boolean DEFAULT false NOT NULL,
    covered_services jsonb NOT NULL,
    uncovered_services jsonb,
    unit_price numeric(10,2),
    currency character varying(3) NOT NULL,
    valid_until date NOT NULL,
    notes text,
    status character varying(255) DEFAULT 'received'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    exchange_rate numeric(10,6),
    unit_price_azn numeric(12,2)
);


ALTER TABLE public.offers OWNER TO sail;

--
-- Name: offers_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.offers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.offers_id_seq OWNER TO sail;

--
-- Name: offers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.offers_id_seq OWNED BY public.offers.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO sail;

--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO sail;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO sail;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: proposal_offer; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.proposal_offer (
    id bigint NOT NULL,
    proposal_id bigint NOT NULL,
    offer_id bigint NOT NULL,
    operator_notes text NOT NULL,
    markup_pct numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    selected_item_types json,
    item_markups jsonb,
    agency_currency_code character(3),
    agency_exchange_rate numeric(12,6)
);


ALTER TABLE public.proposal_offer OWNER TO sail;

--
-- Name: proposal_offer_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.proposal_offer_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.proposal_offer_id_seq OWNER TO sail;

--
-- Name: proposal_offer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.proposal_offer_id_seq OWNED BY public.proposal_offer.id;


--
-- Name: proposals; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.proposals (
    id bigint NOT NULL,
    request_id bigint NOT NULL,
    operator_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    description text NOT NULL,
    total_price numeric(10,2) NOT NULL,
    currency character varying(3) NOT NULL,
    valid_until date NOT NULL,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    original_total_price numeric(12,2),
    original_currency character(3),
    exchange_rate_snapshot numeric(16,6)
);


ALTER TABLE public.proposals OWNER TO sail;

--
-- Name: proposals_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.proposals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.proposals_id_seq OWNER TO sail;

--
-- Name: proposals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.proposals_id_seq OWNED BY public.proposals.id;


--
-- Name: rfq_shared_attachments; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.rfq_shared_attachments (
    rfq_id bigint NOT NULL,
    attachment_id bigint NOT NULL
);


ALTER TABLE public.rfq_shared_attachments OWNER TO sail;

--
-- Name: rfq_supplier; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.rfq_supplier (
    id bigint NOT NULL,
    rfq_id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    sent_at timestamp(0) without time zone,
    token character varying(64),
    token_expires_at timestamp(0) without time zone,
    service_types json,
    notes text
);


ALTER TABLE public.rfq_supplier OWNER TO sail;

--
-- Name: rfq_supplier_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.rfq_supplier_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rfq_supplier_id_seq OWNER TO sail;

--
-- Name: rfq_supplier_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.rfq_supplier_id_seq OWNED BY public.rfq_supplier.id;


--
-- Name: rfqs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.rfqs (
    id bigint NOT NULL,
    request_id bigint NOT NULL,
    operator_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    description text NOT NULL,
    service_type character varying(255) NOT NULL,
    deadline_at date NOT NULL,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.rfqs OWNER TO sail;

--
-- Name: rfqs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.rfqs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rfqs_id_seq OWNER TO sail;

--
-- Name: rfqs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.rfqs_id_seq OWNED BY public.rfqs.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO sail;

--
-- Name: supplier_incidents; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.supplier_incidents (
    id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    severity character varying(255) NOT NULL,
    subject_type character varying(255),
    subject_id bigint,
    context json,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.supplier_incidents OWNER TO sail;

--
-- Name: supplier_incidents_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.supplier_incidents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.supplier_incidents_id_seq OWNER TO sail;

--
-- Name: supplier_incidents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.supplier_incidents_id_seq OWNED BY public.supplier_incidents.id;


--
-- Name: supplier_profiles; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.supplier_profiles (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    service_types jsonb NOT NULL,
    description text,
    website character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.supplier_profiles OWNER TO sail;

--
-- Name: supplier_profiles_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.supplier_profiles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.supplier_profiles_id_seq OWNER TO sail;

--
-- Name: supplier_profiles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.supplier_profiles_id_seq OWNED BY public.supplier_profiles.id;


--
-- Name: supplier_services; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.supplier_services (
    id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    type character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    capacity smallint,
    base_price numeric(10,2),
    currency character(3),
    price_unit character varying(255),
    is_available boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    contact_name character varying(150),
    contact_phone character varying(50)
);


ALTER TABLE public.supplier_services OWNER TO sail;

--
-- Name: supplier_services_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.supplier_services_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.supplier_services_id_seq OWNER TO sail;

--
-- Name: supplier_services_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.supplier_services_id_seq OWNED BY public.supplier_services.id;


--
-- Name: supplier_users; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.supplier_users (
    id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    user_id bigint NOT NULL,
    role character varying(255) DEFAULT 'staff'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT supplier_users_role_check CHECK (((role)::text = ANY ((ARRAY['owner'::character varying, 'manager'::character varying, 'staff'::character varying])::text[])))
);


ALTER TABLE public.supplier_users OWNER TO sail;

--
-- Name: supplier_users_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.supplier_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.supplier_users_id_seq OWNER TO sail;

--
-- Name: supplier_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.supplier_users_id_seq OWNED BY public.supplier_users.id;


--
-- Name: suppliers; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.suppliers (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    phone character varying(50),
    country character(2),
    currency_code character(3) DEFAULT 'AZN'::bpchar NOT NULL,
    service_types jsonb DEFAULT '[]'::jsonb NOT NULL,
    description text,
    website character varying(255),
    is_active boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    uses_portal boolean DEFAULT false NOT NULL,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.suppliers OWNER TO sail;

--
-- Name: suppliers_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.suppliers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.suppliers_id_seq OWNER TO sail;

--
-- Name: suppliers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.suppliers_id_seq OWNED BY public.suppliers.id;


--
-- Name: travel_request_client; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.travel_request_client (
    id bigint NOT NULL,
    travel_request_id bigint NOT NULL,
    client_id bigint NOT NULL,
    is_lead boolean DEFAULT false NOT NULL
);


ALTER TABLE public.travel_request_client OWNER TO sail;

--
-- Name: travel_request_client_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.travel_request_client_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.travel_request_client_id_seq OWNER TO sail;

--
-- Name: travel_request_client_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.travel_request_client_id_seq OWNED BY public.travel_request_client.id;


--
-- Name: travel_requests; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.travel_requests (
    id bigint NOT NULL,
    agency_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    destination character varying(255),
    travel_date_from date,
    travel_date_to date,
    pax_count integer DEFAULT 0 NOT NULL,
    services_needed jsonb,
    notes text,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    pax_count_changed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deadline_at timestamp(0) with time zone
);


ALTER TABLE public.travel_requests OWNER TO sail;

--
-- Name: travel_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.travel_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.travel_requests_id_seq OWNER TO sail;

--
-- Name: travel_requests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.travel_requests_id_seq OWNED BY public.travel_requests.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    role character varying(255) DEFAULT 'agency'::character varying NOT NULL,
    company_name character varying(255),
    phone character varying(255),
    country character varying(2),
    currency_code character(3) DEFAULT 'AZN'::bpchar NOT NULL
);


ALTER TABLE public.users OWNER TO sail;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO sail;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: agencies id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.agencies ALTER COLUMN id SET DEFAULT nextval('public.agencies_id_seq'::regclass);


--
-- Name: agency_users id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.agency_users ALTER COLUMN id SET DEFAULT nextval('public.agency_users_id_seq'::regclass);


--
-- Name: attachments id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.attachments ALTER COLUMN id SET DEFAULT nextval('public.attachments_id_seq'::regclass);


--
-- Name: bookings id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.bookings ALTER COLUMN id SET DEFAULT nextval('public.bookings_id_seq'::regclass);


--
-- Name: clients id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.clients ALTER COLUMN id SET DEFAULT nextval('public.clients_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: markup_settings id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.markup_settings ALTER COLUMN id SET DEFAULT nextval('public.markup_settings_id_seq'::regclass);


--
-- Name: media id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.media ALTER COLUMN id SET DEFAULT nextval('public.media_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: offer_items id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.offer_items ALTER COLUMN id SET DEFAULT nextval('public.offer_items_id_seq'::regclass);


--
-- Name: offers id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.offers ALTER COLUMN id SET DEFAULT nextval('public.offers_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: proposal_offer id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposal_offer ALTER COLUMN id SET DEFAULT nextval('public.proposal_offer_id_seq'::regclass);


--
-- Name: proposals id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposals ALTER COLUMN id SET DEFAULT nextval('public.proposals_id_seq'::regclass);


--
-- Name: rfq_supplier id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_supplier ALTER COLUMN id SET DEFAULT nextval('public.rfq_supplier_id_seq'::regclass);


--
-- Name: rfqs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfqs ALTER COLUMN id SET DEFAULT nextval('public.rfqs_id_seq'::regclass);


--
-- Name: supplier_incidents id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_incidents ALTER COLUMN id SET DEFAULT nextval('public.supplier_incidents_id_seq'::regclass);


--
-- Name: supplier_profiles id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_profiles ALTER COLUMN id SET DEFAULT nextval('public.supplier_profiles_id_seq'::regclass);


--
-- Name: supplier_services id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_services ALTER COLUMN id SET DEFAULT nextval('public.supplier_services_id_seq'::regclass);


--
-- Name: supplier_users id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_users ALTER COLUMN id SET DEFAULT nextval('public.supplier_users_id_seq'::regclass);


--
-- Name: suppliers id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.suppliers ALTER COLUMN id SET DEFAULT nextval('public.suppliers_id_seq'::regclass);


--
-- Name: travel_request_client id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.travel_request_client ALTER COLUMN id SET DEFAULT nextval('public.travel_request_client_id_seq'::regclass);


--
-- Name: travel_requests id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.travel_requests ALTER COLUMN id SET DEFAULT nextval('public.travel_requests_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: agencies; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.agencies (id, name, email, phone, country, currency_code, created_at, updated_at, deleted_at) FROM stdin;
4	Fergana Tourist Group MChJ	tours@ferganatg.uz	+998 73 244 11 22	UZ	AZN	2026-05-22 08:54:54	2026-05-22 10:17:45	\N
3	Samarkand Express Travel MChJ	info@samarkandexpress.uz	+998 66 234 56 78	UZ	USD	2026-05-22 08:54:54	2026-05-26 09:31:21	\N
1	Nomad Travel LLP	groups@nomadtravel.kz	+7 727 344 55 66	KZ	KZT	2026-05-22 08:54:54	2026-05-26 11:38:49	\N
2	AsiaTours Kazakhstan ТОО	booking@asiatours.kz	+7 717 272 10 20	KZ	KZT	2026-05-22 08:54:54	2026-05-27 19:27:07	\N
5	dfdf	dfdf@dfd.ru	5345545	CN	AZN	2026-06-01 04:29:49	2026-06-01 04:29:49	\N
\.


--
-- Data for Name: agency_users; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.agency_users (id, agency_id, user_id, role, created_at, updated_at) FROM stdin;
1	1	2	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
2	2	3	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
3	3	4	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
4	4	5	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
5	2	13	staff	2026-05-26 09:28:32	2026-05-26 09:28:32
6	1	15	staff	2026-05-28 05:18:32	2026-05-28 05:18:32
7	5	16	owner	2026-06-01 04:29:49	2026-06-01 04:29:49
\.


--
-- Data for Name: attachments; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.attachments (id, attachable_type, attachable_id, uploader_id, disk, path, filename, mime_type, size, created_at, updated_at) FROM stdin;
1	App\\Domain\\Requests\\Models\\TravelRequest	1	2	local	attachments/requests/1/eAXyn7bw4O5PgK3FURoi2ewV6KUnb9U3ysPpApGI.jpg	home3-offer-img1.jpg	image/jpeg	73789	2026-05-30 09:03:25	2026-05-30 09:03:25
2	App\\Domain\\Requests\\Models\\TravelRequest	2	2	local	attachments/requests/2/MGzECLOD3jOtdPRzmx6EAj19qmQhXChjpMO4a9yw.jpg	home3-offer-img2.jpg	image/jpeg	70412	2026-05-30 10:50:33	2026-05-30 10:50:33
3	App\\Domain\\Requests\\Models\\TravelRequest	2	2	local	attachments/requests/2/ULaWOIhPFEgTS9u1HYgLyaokcpC70UD8rd3HBALE.docx	фразовые глаголы.docx	application/vnd.openxmlformats-officedocument.wordprocessingml.document	17820	2026-05-30 10:54:31	2026-05-30 10:54:31
5	\N	\N	1	local	attachments/temp/RXRPsCvxwX9BRXp2SH5ZjumqLevIHzx8u4ENDapt.jpg	12c46c3270fa5526a57bcfdf8124d593.jpg	image/jpeg	10298	2026-05-30 17:40:18	2026-05-30 17:40:18
6	\N	\N	1	local	attachments/temp/uqtF8OHz5V6s2dW0KJ1VUZlvXoknhBpmO1BW14S1.jpg	12c46c3270fa5526a57bcfdf8124d593.jpg	image/jpeg	10298	2026-05-30 17:43:18	2026-05-30 17:43:18
7	\N	\N	9	local	attachments/temp/kv2dRcmKn9B0AERm4SeSI1YNMjhpUNr0BcVGNP8d.jpg	Image 6.jpg	image/jpeg	3151760	2026-05-30 21:40:39	2026-05-30 21:40:39
8	\N	\N	9	local	attachments/temp/s4UELoAFHec0m7m4TgZJL0qFQ9Zu2gxpOlFhZxsx.jpg	Image 3.jpg	image/jpeg	1172254	2026-05-30 21:49:11	2026-05-30 21:49:11
9	App\\Domain\\Offers\\Models\\Offer	1001	9	local	attachments/temp/QArccMC4ugVBhN7je1gTZQwhznelbgQ9UvjssRFQ.jpg	Image 2.jpg	image/jpeg	1678948	2026-05-30 21:53:51	2026-05-30 21:54:00
10	App\\Domain\\Proposals\\Models\\Proposal	1002	1	local	attachments/proposals/1002/OQ8Q7DrLezDmd4wa9fI0x6AUr6HHg8KWpwDzzH6s.pdf	Smart Pass v3 System Basic Operation Manual.pdf	application/pdf	2850383	2026-05-31 16:28:43	2026-05-31 16:28:43
\.


--
-- Data for Name: bookings; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.bookings (id, proposal_id, request_id, agency_id, operator_id, confirmed_at, travel_date_from, travel_date_to, pax_count, final_price, currency, status, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.cache (key, value, expiration) FROM stdin;
caspirex-connect-cache-f1f70ec40aaa556905d4a030501c0ba4	i:8;	1780288232
caspirex-connect-cache-a75f3f172bfb296f2e10cbfc6dfc1883:timer	i:1780254210;	1780254210
caspirex-connect-cache-5c785c036466adea360111aa28563bfd556b5fba:timer	i:1780254210;	1780254210
caspirex-connect-cache-a75f3f172bfb296f2e10cbfc6dfc1883	i:2;	1780254210
caspirex-connect-cache-5c785c036466adea360111aa28563bfd556b5fba	i:2;	1780254210
caspirex-connect-cache-e9b6cc1432541b9ceebf113eee05eeba:timer	i:1780255035;	1780255035
caspirex-connect-cache-e9b6cc1432541b9ceebf113eee05eeba	i:4;	1780255035
caspirex-connect-cache-830b3b63aeb7c393f1a75d0894c14921:timer	i:1780255038;	1780255038
caspirex-connect-cache-830b3b63aeb7c393f1a75d0894c14921	i:3;	1780255038
caspirex-connect-cache-2ab11b14c804b287aed224f22b66173f:timer	i:1780288231;	1780288231
caspirex-connect-cache-2ab11b14c804b287aed224f22b66173f	i:1;	1780288231
caspirex-connect-cache-f234eb75fd21931f200245e011453a83:timer	i:1780288231;	1780288231
caspirex-connect-cache-f234eb75fd21931f200245e011453a83	i:1;	1780288231
caspirex-connect-cache-c1de3da8036dabfda8b2027cfb5d32fa:timer	i:1780288231;	1780288231
caspirex-connect-cache-c1de3da8036dabfda8b2027cfb5d32fa	i:1;	1780288231
caspirex-connect-cache-f1f70ec40aaa556905d4a030501c0ba4:timer	i:1780288232;	1780288232
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: clients; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.clients (id, agency_id, name, email, phone, passport_number, nationality, date_of_birth, notes, created_at, updated_at, deleted_at) FROM stdin;
1	1	Нурлан Сейткали	n.seitkali@nomadtravel.kz	+7 701 234 56 78	N12345678	KZ	1982-04-15	Руководитель группы. VIP-клиент. Предпочитает номера с видом на море. Халяльное питание.	2026-05-22 08:54:56	2026-05-22 08:54:56	\N
2	1	Айгерим Бекова	a.bekova@nomadtravel.kz	+7 702 876 54 32	N87654321	KZ	1990-08-20	\N	2026-05-22 08:54:56	2026-05-22 08:54:56	\N
3	1	Марат Джумагалиев	m.dzhumagaliev@nomadtravel.kz	+7 705 111 22 33	N11223344	KZ	1977-12-03	\N	2026-05-22 08:54:56	2026-05-22 08:54:56	\N
4	2	Данияр Ахметов	d.akhmetov@asiatours.kz	+7 717 300 11 22	N55443322	KZ	1968-05-10	Руководитель делегации. Требуется протокольная встреча в аэропорту. Деловая программа.	2026-05-22 08:54:56	2026-05-22 08:54:56	\N
5	2	Зульфия Касымова	z.kassymova@asiatours.kz	+7 701 555 66 77	N66778899	KZ	1985-02-28	\N	2026-05-22 08:54:56	2026-05-22 08:54:56	\N
6	3	Бобур Каримов	b.karimov@samarkandexpress.uz	+998 91 234 56 78	AA1234567	UZ	1979-09-14	Организатор тура для группы паломников. Важно — близость к мечетям.	2026-05-22 08:54:56	2026-05-22 08:54:56	\N
7	3	Дилноза Юсупова	d.yusupova@samarkandexpress.uz	+998 93 987 65 43	AA7654321	UZ	1994-03-07	\N	2026-05-22 08:54:56	2026-05-22 08:54:56	\N
\.


--
-- Data for Name: currencies; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.currencies (code, name, rate, is_active, is_default, rates_updated_at, created_at, updated_at) FROM stdin;
AZN	Манат	1.000000	t	t	\N	2026-05-26 05:19:33	2026-05-26 05:19:33
USD	Доллар США	1.700000	t	f	2026-05-31 18:48:21	2026-05-26 05:19:33	2026-05-31 18:48:21
EUR	Евро	1.977000	t	f	2026-05-31 18:48:21	2026-05-26 05:19:33	2026-05-31 18:48:21
GBP	Фунт стерлингов	2.291700	f	f	2026-05-31 18:48:21	2026-05-26 05:19:33	2026-05-31 18:48:21
RUB	Российский рубль	0.023795	f	f	2026-05-31 18:48:21	2026-05-26 05:19:33	2026-05-31 18:48:21
TRY	Турецкая лира	0.037000	f	f	2026-05-31 18:48:21	2026-05-26 05:19:33	2026-05-31 18:48:21
KZT	Казахстанский тенге	0.003597	t	f	2026-05-31 18:48:21	2026-05-26 05:22:55	2026-05-31 18:48:21
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: markup_settings; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.markup_settings (id, service_type, markup_pct, created_at, updated_at) FROM stdin;
1	accommodation	18.00	2026-05-22 08:54:56	2026-05-22 08:54:56
3	guide	15.00	2026-05-22 08:54:56	2026-05-22 08:54:56
4	activity	20.00	2026-05-22 08:54:56	2026-05-22 08:54:56
5	other	15.00	2026-05-22 08:54:56	2026-05-22 08:54:56
2	transport	10.00	2026-05-22 08:54:56	2026-05-22 18:25:50
\.


--
-- Data for Name: media; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.media (id, model_type, model_id, uuid, collection_name, name, file_name, mime_type, disk, conversions_disk, size, manipulations, custom_properties, generated_conversions, responsive_images, order_column, created_at, updated_at) FROM stdin;
12	App\\Domain\\Agencies\\Models\\Agency	1	7706dbd8-1b0b-45b3-ad2f-f26f12f7087e	avatar	b-sm	b-sm.jpg	image/jpeg	public	public	2218	[]	[]	[]	[]	1	2026-05-25 10:20:13	2026-05-25 10:20:13
4	App\\Domain\\Suppliers\\Models\\Supplier	1	6cda6671-6f59-4686-8e59-c385eabc80fb	avatar	Image 1	Image-1.jpg	image/jpeg	public	public	262577	[]	[]	[]	[]	2	2026-05-07 12:42:29	2026-05-07 12:42:29
11	App\\Domain\\Suppliers\\Models\\Supplier	4	f3bbd277-cb3c-490c-90f1-ac265a85f243	avatar	f-sm	f-sm.jpg	image/jpeg	public	public	19037	[]	[]	[]	[]	5	2026-05-25 06:32:58	2026-05-25 06:32:58
13	App\\Domain\\Agencies\\Models\\Agency	2	234b02ad-e6ae-4677-aaf9-abd9fcbd30bb	avatar	Screenshot 2026-04-11 101520	Screenshot-2026-04-11-101520.png	image/png	public	public	267845	[]	[]	[]	[]	1	2026-05-27 19:19:15	2026-05-27 19:19:15
14	App\\Domain\\Suppliers\\Models\\SupplierService	10	8d4a52d0-3995-410a-a749-da8083f2490f	photos	destination-img5	destination-img5.jpg	image/jpeg	public	public	74789	[]	[]	[]	[]	1	2026-05-29 06:14:53	2026-05-29 06:14:53
15	App\\Domain\\Suppliers\\Models\\SupplierService	10	f6f00702-60ff-41ef-8d4e-903621fae5b4	photos	destination-img4	destination-img4.jpg	image/jpeg	public	public	75253	[]	[]	[]	[]	1	2026-05-29 06:14:53	2026-05-29 06:14:53
16	App\\Domain\\Suppliers\\Models\\SupplierService	10	8c04eed9-376d-40fb-9057-fc1ebccdee72	photos	destination-img6	destination-img6.jpg	image/jpeg	public	public	61901	[]	[]	[]	[]	2	2026-05-29 06:14:55	2026-05-29 06:14:55
17	App\\Domain\\Suppliers\\Models\\SupplierService	25	3f5cc031-07eb-4e15-b430-9055f757e1b7	photos	Image 1	Image-1.jpg	image/jpeg	public	public	262577	[]	[]	[]	[]	1	2026-05-31 12:47:52	2026-05-31 12:47:52
18	App\\Domain\\Suppliers\\Models\\SupplierService	25	7c48a679-15b7-481d-9e90-196d064048cb	photos	Image 4	Image-4.jpg	image/jpeg	public	public	2027960	[]	[]	[]	[]	2	2026-05-31 12:47:52	2026-05-31 12:47:52
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_05_06_000001_add_role_columns_to_users_table	2
12	2026_05_06_000002_create_travel_requests_table	3
13	2026_05_06_000003_create_rfqs_table	3
14	2026_05_06_000004_create_rfq_supplier_table	3
15	2026_05_06_000005_create_offers_table	3
16	2026_05_06_000006_create_proposals_table	3
17	2026_05_06_000007_create_proposal_offer_table	3
18	2026_05_06_000008_create_bookings_table	3
19	2026_05_06_095133_create_personal_access_tokens_table	4
20	2026_05_07_000001_create_supplier_profiles_table	5
21	2026_05_07_000002_change_services_needed_to_json_in_travel_requests	6
22	2026_05_07_000003_change_service_columns_to_json_in_offers	6
23	2026_05_07_000004_create_attachments_table	6
24	2026_05_07_000005_create_clients_table	6
25	2026_05_07_000006_create_travel_request_client_table	6
26	2026_05_07_000007_convert_json_columns_to_jsonb	7
27	2026_05_07_000008_create_supplier_services_table	8
28	2026_05_07_000009_create_offer_items_table	8
29	2026_05_07_000010_make_offer_unit_price_nullable	8
30	2026_05_07_000011_add_supplier_tokens_to_rfq_supplier_table	9
31	2026_05_07_000012_create_markup_settings_table	10
32	2026_05_07_000013_add_markup_pct_to_proposal_offer_table	10
33	2026_05_07_000014_add_deadline_at_to_travel_requests_table	11
34	2026_05_07_121749_create_media_table	12
35	2026_05_22_000001_add_country_to_users_table	13
36	2026_05_22_000002_drop_regions_from_supplier_profiles_table	13
37	2026_05_22_000001_add_selected_item_types_to_proposal_offer_table	14
38	2026_05_22_000002_add_item_markups_to_proposal_offer_table	15
39	2026_05_26_000001_create_currencies_table	16
40	2026_05_26_000002_add_currency_code_to_users_table	16
41	2026_05_26_000003_add_currency_snapshot_to_offer_items_table	16
42	2026_05_26_000004_add_currency_snapshot_to_proposal_offer_table	16
43	2026_05_26_200001_create_agencies_table	17
44	2026_05_26_200002_create_suppliers_table	18
45	2026_05_26_200003_migrate_agencies_and_suppliers_data	19
46	2026_05_26_200004_add_service_types_to_rfq_supplier_table	20
47	2026_05_26_200005_add_notes_to_rfq_supplier_table	21
48	2026_05_26_200006_add_currency_snapshot_to_proposals_table	22
49	2026_05_26_200007_add_uses_portal_to_suppliers_table	23
50	2026_05_29_000001_make_supplier_service_price_fields_nullable	24
51	2026_05_29_000002_add_contact_fields_to_supplier_services	25
52	2026_05_30_000001_create_supplier_incidents_table	26
53	2026_05_30_000002_make_attachable_nullable	27
54	2026_05_30_000003_create_rfq_shared_attachments_table	28
55	2026_05_30_000010_set_auto_increment_1001_on_main_tables	29
56	2026_05_30_000011_add_exchange_rate_to_offers_table	29
57	2026_05_31_000001_add_soft_deletes_to_business_tables	30
\.


--
-- Data for Name: offer_items; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.offer_items (id, offer_id, supplier_service_id, type, name, description, quantity, unit_price, currency, price_unit, created_at, updated_at, unit_price_azn, exchange_rate) FROM stdin;
1	1005	25	accommodation	Домик в лесу	\N	1	40.00	AZN	fixed	2026-05-31 16:10:48	2026-05-31 16:10:48	40.00	1.000000
\.


--
-- Data for Name: offers; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.offers (id, rfq_id, supplier_id, is_partial, covered_services, uncovered_services, unit_price, currency, valid_until, notes, status, created_at, updated_at, exchange_rate, unit_price_azn) FROM stdin;
1002	3	4	f	["guide"]	\N	60.00	AZN	2026-06-02	Гид: Ramin (RU/EN)\n\nТранспорт: Mercedes Sprinter 19 pax\n\nВсе будет на самом высшем уровне. Прикрепляем файлы-фотки	withdrawn	2026-05-30 22:27:29	2026-05-30 22:27:33	\N	\N
1004	1	4	f	["accommodation"]	\N	40.00	AZN	2026-06-02	Проживание: Домик в лесу\n\nВсе будет на самом высшем уровне. Прикрепляем файлы-фотки	withdrawn	2026-05-31 12:48:30	2026-05-31 16:10:30	\N	\N
1005	1	4	f	["accommodation"]	\N	40.00	AZN	2026-06-02	Проживание: Домик в лесу\n\nВсе будет на самом высшем уровне. Прикрепляем файлы-фотки	selected	2026-05-31 16:10:48	2026-05-31 16:28:43	\N	\N
1003	3	4	f	["guide"]	\N	65.00	AZN	2026-06-02	Гид: Ramin (RU/EN)\n\nВсе будет на самом высшем уровне. Прикрепляем файлы-фотки	selected	2026-05-31 07:07:00	2026-05-31 17:24:32	\N	\N
1001	2	4	f	["transport"]	\N	190.00	AZN	2026-06-02	Транспорт: Mercedes Sprinter 19 pax\n\nВсе будет на самом высшем уровне. Прикрепляем файлы-фотки	selected	2026-05-30 21:54:00	2026-05-31 17:24:32	\N	\N
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
1	App\\Domain\\Users\\Models\\User	1	api	7904c86680cd7930b43b2905bdf8442b237f8fe2d8914efc860145fad28f6451	["*"]	\N	\N	2026-05-22 09:03:11	2026-05-22 09:03:11
2	App\\Domain\\Users\\Models\\User	1	api	580772df9b5c7fa97684198143568f7613f381f6d990c096ee167b816fc9965e	["*"]	\N	\N	2026-05-22 16:43:14	2026-05-22 16:43:14
3	App\\Domain\\Users\\Models\\User	1	api	500d52ad33bc57c1fa41c52ffbaabf87614521636c5536f04c4965caf3aa0cb5	["*"]	\N	\N	2026-05-23 05:46:06	2026-05-23 05:46:06
4	App\\Domain\\Users\\Models\\User	1	api	4cdf2a3dfcf9236b78895317114a26c8907fcfd75c37fcced50162beb75a2186	["*"]	\N	\N	2026-05-23 14:21:57	2026-05-23 14:21:57
5	App\\Domain\\Users\\Models\\User	2	api	84875ec1e3ef1d9d7b2104922c83a986a5f53aba8d8ab679bd2691ba7527de5b	["*"]	\N	\N	2026-05-23 14:40:33	2026-05-23 14:40:33
6	App\\Domain\\Users\\Models\\User	2	api	e9e67cb7a4899cc78caa9723b6859ddc369bce820d58cd3b9f868ce337c3935f	["*"]	\N	\N	2026-05-23 14:40:41	2026-05-23 14:40:41
7	App\\Domain\\Users\\Models\\User	2	api	07fa1f27a34b08e159a51cb3c1188dbbeaddb9caf90be505b209e4aabf6d1086	["*"]	\N	\N	2026-05-23 14:40:59	2026-05-23 14:40:59
8	App\\Domain\\Users\\Models\\User	2	api	29199890584bd6a0a98bfcb83195ed57c181633868c019ae2221a046e9372eb8	["*"]	\N	\N	2026-05-23 14:43:03	2026-05-23 14:43:03
9	App\\Domain\\Users\\Models\\User	2	api	6f9bea67cce8c569407f68dbf6854c98b62988d4759dfe079a6bf1e56c676f5f	["*"]	\N	\N	2026-05-23 14:45:01	2026-05-23 14:45:01
10	App\\Domain\\Users\\Models\\User	2	api	fe55dc3a2c08b55e431d18a27aa32090e5c15716caf2fffd18c45a4c79211c90	["*"]	\N	\N	2026-05-23 18:58:11	2026-05-23 18:58:11
11	App\\Domain\\Users\\Models\\User	2	api	5041ab9b639c01cc0fb6ad0f70602403c6617df4ebdbb9498d2ffcec427efe25	["*"]	\N	\N	2026-05-24 04:57:54	2026-05-24 04:57:54
12	App\\Domain\\Users\\Models\\User	1	api	7faedb93ebd596e1dce00e982a42b27b7508d43c47510c7291398ca8b49ab328	["*"]	\N	\N	2026-05-24 04:58:12	2026-05-24 04:58:12
13	App\\Domain\\Users\\Models\\User	1	api	825546fb6def712ccd8c7fb75648655a894ba63bf2732c33746935bf0b4bb372	["*"]	\N	\N	2026-05-25 04:47:58	2026-05-25 04:47:58
14	App\\Domain\\Users\\Models\\User	4	api	d6fbfb8517a4756094e6805fd86e19350a3b39cd6d0abe2bb2aaabd4c3578ca3	["*"]	\N	\N	2026-05-25 13:45:03	2026-05-25 13:45:03
15	App\\Domain\\Users\\Models\\User	1	api	f5be4087e86f1e3270c790bef396965e74f2e43fc24ed923dd1959dee3b56a9d	["*"]	\N	\N	2026-05-26 05:18:06	2026-05-26 05:18:06
16	App\\Domain\\Users\\Models\\User	1	api	1932245bd780c758d9635d5425351153bf26df9a1c3c78732a786f0e5646e8d9	["*"]	\N	\N	2026-05-26 17:05:13	2026-05-26 17:05:13
17	App\\Domain\\Users\\Models\\User	1	api	f1600e0bf13e9bf1554d9fced9e1e87af120d15f834049acaef4c4772a86a33a	["*"]	\N	\N	2026-05-27 05:27:57	2026-05-27 05:27:57
18	App\\Domain\\Users\\Models\\User	1	api	d513524667803fea1832cf45bcf6655a128017641791dabdb9f3e8cc64b07064	["*"]	\N	\N	2026-05-27 10:21:53	2026-05-27 10:21:53
19	App\\Domain\\Users\\Models\\User	1	api	4f7e426a79457da6701219a4aa939120f69c0b01d22f421dbb480ebcedd8121d	["*"]	\N	\N	2026-05-27 10:42:59	2026-05-27 10:42:59
20	App\\Domain\\Users\\Models\\User	1	api	ec27fb9a6059219fc529d24c69729c35a8915edb36509f056a805814b19aebd9	["*"]	\N	\N	2026-05-27 10:43:58	2026-05-27 10:43:58
21	App\\Domain\\Users\\Models\\User	1	api	e77acc2d96889a0171ba9ba4d489ebe0eed22668ba318593ef94cc8471579c3d	["*"]	\N	\N	2026-05-27 10:46:21	2026-05-27 10:46:21
22	App\\Domain\\Users\\Models\\User	1	api	05034bfbe666d47aa3f123870460d55570c144916c80b34a14e2f510037d68b9	["*"]	\N	\N	2026-05-27 14:45:51	2026-05-27 14:45:51
23	App\\Domain\\Users\\Models\\User	1	api	7ab8bf0e23d13a104d7609bd8015312bc08ea263f1e9c3bd21a4811fea1ba784	["*"]	\N	\N	2026-05-27 18:01:19	2026-05-27 18:01:19
24	App\\Domain\\Users\\Models\\User	1	api	def176a8fdf7c0e78a0f55ecd3cd42a7ca6d26efdbf7e741391edc354ad5b8d8	["*"]	2026-05-27 18:23:59	\N	2026-05-27 18:23:52	2026-05-27 18:23:59
25	App\\Domain\\Users\\Models\\User	2	api	1364f41b0c2e7b261d65714a1e1ddf59af8dccdfc5bbb763499c6356b0d995dd	["*"]	\N	\N	2026-05-28 04:32:14	2026-05-28 04:32:14
26	App\\Domain\\Users\\Models\\User	2	api	81f4c159a935d0402d55439c60efe0ec7969f7d1b1e0233f73a0690a1d0088a8	["*"]	\N	\N	2026-05-28 13:37:20	2026-05-28 13:37:20
27	App\\Domain\\Users\\Models\\User	1	api	4f5aa24ff9a9832c2f4fa5332854846066353a91a8fe03a99b9732fc17c9e17a	["*"]	\N	\N	2026-05-28 15:07:03	2026-05-28 15:07:03
28	App\\Domain\\Users\\Models\\User	2	api	c497f56ff5fda39b609c74e78764cbd282ffe9ffc1cb2f729cdc41d0b834ac58	["*"]	\N	\N	2026-05-28 15:07:26	2026-05-28 15:07:26
29	App\\Domain\\Users\\Models\\User	1	api	b0df6798f2a317c21f65d7c4f0cc41a7862044e2137abf42af6f4fc84dee5aa4	["*"]	\N	\N	2026-05-28 15:09:09	2026-05-28 15:09:09
30	App\\Domain\\Users\\Models\\User	9	api	25a5b6dd57a3483cf73c1db114ae295ae944a6a6385af1f84cb5070574d35b4f	["*"]	\N	\N	2026-05-28 18:51:18	2026-05-28 18:51:18
31	App\\Domain\\Users\\Models\\User	9	api	bfa717a2647fa411d4fcde1e446d1f0ad35bab2a6105d45ff136929a7ed3eaef	["*"]	\N	\N	2026-05-28 18:59:32	2026-05-28 18:59:32
32	App\\Domain\\Users\\Models\\User	9	api	6d9ecebaa69dec6ca41d9ebc0aa9e1690f35be063d4b64c0dab2f7ca2d6dc6db	["*"]	\N	\N	2026-05-28 19:33:41	2026-05-28 19:33:41
33	App\\Domain\\Users\\Models\\User	9	api	bfe2d6f1dc4e4cd81d7ddfcb227b36f59264ee293d48cf7905c80210e50d3c57	["*"]	\N	\N	2026-05-29 04:58:53	2026-05-29 04:58:53
34	App\\Domain\\Users\\Models\\User	9	api	bf2860f88b8de471a2aa1f0951d18228f4a1f76c90047c96103361ec01f96726	["*"]	\N	\N	2026-05-29 09:28:38	2026-05-29 09:28:38
35	App\\Domain\\Users\\Models\\User	1	api	4d6d8a89b04bfd258413dc8bc42106a4de2f7af2a74a6c9f299bffe2840ed8e4	["*"]	\N	\N	2026-05-29 09:31:28	2026-05-29 09:31:28
36	App\\Domain\\Users\\Models\\User	2	api	b658f60e8d7bf96bf1fb8543b997f5664a651bd7b490deb1a8232944327c6499	["*"]	\N	\N	2026-05-29 16:20:15	2026-05-29 16:20:15
37	App\\Domain\\Users\\Models\\User	9	api	a592fc807dfbb81472cc5e63bbe8b07c90d52ee680e34471a7dc0557b025baff	["*"]	\N	\N	2026-05-29 16:21:35	2026-05-29 16:21:35
39	App\\Domain\\Users\\Models\\User	7	api	2078e816a541bc384dc9c1eeb821ac6fdb179a1304a88413d1cc12e3d900b10b	["*"]	\N	\N	2026-05-29 16:37:51	2026-05-29 16:37:51
42	App\\Domain\\Users\\Models\\User	6	api	04c8e015d74a08a033bfe648e5ffec38590fe6ef60c9b5d1692ce9694ccbbd40	["*"]	2026-05-30 08:47:00	\N	2026-05-30 08:42:43	2026-05-30 08:47:00
38	App\\Domain\\Users\\Models\\User	6	api	e808134d5ce310771b8b5e56b5bcac0a340d6da7ee057b0981d8f048ee6dae15	["*"]	2026-05-29 16:40:10	\N	2026-05-29 16:37:50	2026-05-29 16:40:10
40	App\\Domain\\Users\\Models\\User	9	api	119a035a28d3eb282c4067ee635278dc3f21aa75451b641e23514227972072a1	["*"]	\N	\N	2026-05-30 07:17:51	2026-05-30 07:17:51
41	App\\Domain\\Users\\Models\\User	6	api	969a81c609215578301cac477af4b766a8de681d70cc4e13534687648194499a	["*"]	\N	\N	2026-05-30 08:42:36	2026-05-30 08:42:36
44	App\\Domain\\Users\\Models\\User	6	api	95ef80c810024c69f6de7d90ebbec7de60cc121ab88b9599bd8afea230501d7e	["*"]	2026-05-30 08:53:52	\N	2026-05-30 08:53:52	2026-05-30 08:53:52
43	App\\Domain\\Users\\Models\\User	1	api	bc83c7e53df93b5f26e7a0f6e056278b7263b799552beb770946db1d6300e602	["*"]	\N	\N	2026-05-30 08:44:03	2026-05-30 08:44:03
45	App\\Domain\\Users\\Models\\User	6	api	be576646881adc195511dadd0199c0110eea9b56f8cd0911afb29d0980134660	["*"]	2026-05-30 08:54:17	\N	2026-05-30 08:54:17	2026-05-30 08:54:17
46	App\\Domain\\Users\\Models\\User	2	api	4227711777bde07b28fd0f68c80e173656454c52d2e3fb7987c4f5ccdd29f175	["*"]	\N	\N	2026-05-30 09:00:28	2026-05-30 09:00:28
47	App\\Domain\\Users\\Models\\User	6	api	b79d2a868c7aab6f7ccce3dbea3b4ce6f6955059c28e212e39c35e79ddf1fc00	["*"]	2026-05-30 09:16:00	\N	2026-05-30 09:16:00	2026-05-30 09:16:00
48	App\\Domain\\Users\\Models\\User	2	api	0a6ff5fd3a3c5977093f72229107252908dead8bb7bbbd3ccbec9a888697d1f7	["*"]	2026-05-30 09:17:44	\N	2026-05-30 09:17:44	2026-05-30 09:17:44
49	App\\Domain\\Users\\Models\\User	2	api	3ee7a357b5712c6f0ebdb873af453d826e13cb41e9f218eb384e27fabb1c5571	["*"]	2026-05-30 09:18:46	\N	2026-05-30 09:18:46	2026-05-30 09:18:46
50	App\\Domain\\Users\\Models\\User	2	api	be2867ceabbd8b9fab13f50da66d294671665481d0100db41dc55bbb8de51fa7	["*"]	2026-05-30 09:19:11	\N	2026-05-30 09:19:11	2026-05-30 09:19:11
51	App\\Domain\\Users\\Models\\User	2	api	25bd3771637b33d100a39fce9fb04aa4766e6be6e14dd18b0dc0a91f4535aea7	["*"]	2026-05-30 09:19:21	\N	2026-05-30 09:19:21	2026-05-30 09:19:21
52	App\\Domain\\Users\\Models\\User	1	api	8fa7afb5b55197d02c3ffd4a6541e8a1994f886e08c8e8b9fc2acb3076badfde	["*"]	\N	\N	2026-05-30 11:26:53	2026-05-30 11:26:53
53	App\\Domain\\Users\\Models\\User	2	api	ed3b32c9ba9b16220ee3404f2df8049181e3d2b9d12213d370b84681aec61871	["*"]	\N	\N	2026-05-30 16:30:25	2026-05-30 16:30:25
54	App\\Domain\\Users\\Models\\User	1	api	46aad6c5f14f62dd59b63c240b0c98182915776c2ae57e26123c7b07dc242d3f	["*"]	\N	\N	2026-05-30 16:30:39	2026-05-30 16:30:39
55	App\\Domain\\Users\\Models\\User	9	api	6ee06bcbc1d2e89ead074f5e9af8da2b580fcf0c0ecf570812650d6f2ab9dcf0	["*"]	\N	\N	2026-05-30 17:42:05	2026-05-30 17:42:05
56	App\\Domain\\Users\\Models\\User	1	api	fd1e51f25cb6cfcc32dc03b5ab3f1760454694705535364482a3c91a17362134	["*"]	\N	\N	2026-05-30 22:23:30	2026-05-30 22:23:30
57	App\\Domain\\Users\\Models\\User	9	api	0d57153a095852698701583420143b020f22467f6cc9fd6a9393d41e99d7053b	["*"]	\N	\N	2026-05-31 06:49:19	2026-05-31 06:49:19
58	App\\Domain\\Users\\Models\\User	1	api	a3bbdfd818ee89200ded1b54fee8c5430db332f14de8027e0143e5573d0e1a2a	["*"]	\N	\N	2026-05-31 06:50:44	2026-05-31 06:50:44
59	App\\Domain\\Users\\Models\\User	1	api	a11540ccca588ab5a514a27f31c9d49b6a4ba4b067fd27c46d310b681a04f027	["*"]	\N	\N	2026-05-31 12:29:24	2026-05-31 12:29:24
60	App\\Domain\\Users\\Models\\User	9	api	f031eeccfe5faac721a0421b124917a948ff1a4e88a184c99d6e87b9b174e900	["*"]	\N	\N	2026-05-31 12:46:14	2026-05-31 12:46:14
61	App\\Domain\\Users\\Models\\User	9	api	a4f2b2e97b21c32fd5317533fcfd895870afe5cc0e46cba985cd0573fd3fd892	["*"]	\N	\N	2026-05-31 16:09:41	2026-05-31 16:09:41
62	App\\Domain\\Users\\Models\\User	1	api	d326d2357c3813818cc75ef917e99ce2810fb05679fe18c2b02f0da923116670	["*"]	\N	\N	2026-05-31 16:11:03	2026-05-31 16:11:03
63	App\\Domain\\Users\\Models\\User	2	api	1aad58c69f4fbb172228bc202e4f4583ceb341ba3a6078a15085e3016d7b4dd3	["*"]	\N	\N	2026-05-31 17:30:43	2026-05-31 17:30:43
66	App\\Domain\\Users\\Models\\User	1	api	94afe0499672739caa69004b669793883e0ffe91141225c58404417293aa7952	["*"]	\N	\N	2026-06-01 04:29:31	2026-06-01 04:29:31
\.


--
-- Data for Name: proposal_offer; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.proposal_offer (id, proposal_id, offer_id, operator_notes, markup_pct, selected_item_types, item_markups, agency_currency_code, agency_exchange_rate) FROM stdin;
1	1002	1005		0.00	["accommodation"]	{"accommodation": 10}	\N	\N
2	1002	1003		15.00	\N	\N	\N	\N
3	1002	1001		10.00	\N	\N	\N	\N
\.


--
-- Data for Name: proposals; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.proposals (id, request_id, operator_id, title, description, total_price, currency, valid_until, status, created_at, updated_at, original_total_price, original_currency, exchange_rate_snapshot) FROM stdin;
1002	2	1	Nomad Travel LLP, Гастрономический тур по Азербайджану, 05.06.2026 – 06.07.2026	Примечания для агентства	91117.60	KZT	2026-06-30	sent	2026-05-31 16:28:43	2026-05-31 17:30:16	327.75	AZN	0.003597
\.


--
-- Data for Name: rfq_shared_attachments; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.rfq_shared_attachments (rfq_id, attachment_id) FROM stdin;
1	3
1	2
1	6
2	3
2	2
2	6
3	3
3	2
3	6
\.


--
-- Data for Name: rfq_supplier; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.rfq_supplier (id, rfq_id, supplier_id, sent_at, token, token_expires_at, service_types, notes) FROM stdin;
1	1	3	2026-05-30 17:43:25	DdD21mya8y1dKIlyMDrij8ezC9aE6c5WwXz1sNmJeCKKsBchcGHaGhJrMd2AilPt	2026-06-05 00:00:00	\N	\N
2	1	1	2026-05-30 17:43:25	LrgXegJiDeEzWzrLcHjv69W3Ps6rwNIEJXSpksBsVUphHTdksYCtf6Wv7i2ziCxU	2026-06-05 00:00:00	\N	\N
3	1	4	2026-05-30 17:43:25	3wMMW4sqH78T7h0DpyDFIAz4rG9CRK1smy7lZSbLiGw7beh4PCoEcdTYYNuMLBnX	2026-06-05 00:00:00	\N	\N
4	1	2	2026-05-30 17:43:25	JsMNIlVza5zmWs3aQ5htUI27HtbhSAkGxeiKAOeA3CwgiW4wsk2pK7veIcageskW	2026-06-05 00:00:00	\N	\N
5	2	5	2026-05-30 17:43:25	kTKXbjJRUDP2nmQHFFKpj5m2YkwKVE6bXikGf0JExUlSCd6Uo6GlU2Wu0x2Usk00	2026-06-05 00:00:00	\N	\N
6	2	8	2026-05-30 17:43:25	20LFpsiNoBoaaad2jkDLgaO8BLo7KLacjEklZpbCrPoTZH24L2O0AnmnMXyX2zP3	2026-06-05 00:00:00	\N	\N
7	2	1	2026-05-30 17:43:25	CXGHaW5GsWkL6APEKDJBDKcVsZKBBGyzzLS5dr8oZu1FrhGzFtXctDF6VgKdNpAx	2026-06-05 00:00:00	\N	\N
8	2	4	2026-05-30 17:43:25	duqtEE7GD1HVW4R5h3VZAeVtvHXtGT3lNeenGlj006qs7SJ3WUuG7oje0TruOhbS	2026-06-05 00:00:00	\N	\N
9	3	6	2026-05-30 17:43:25	sHnVLk9qMLCGQ0FJkb8C7th3zYj1nfJeF710FoRm33QIhBUPhkjWKeMZYXnfqUwg	2026-06-05 00:00:00	\N	\N
10	3	7	2026-05-30 17:43:25	xjowDOwf14rgvEn3PwqV4ebjSd8ygjNl3TyCxplKXavlwYmSLiWY6OyZgVDUIlQG	2026-06-05 00:00:00	\N	\N
11	3	8	2026-05-30 17:43:25	pPkCOYBjLfXI1bXdXP9rYqZak9p2RT6Yo3OyKKpAtsCAsLJwpnLtGaazJZWJi82w	2026-06-05 00:00:00	\N	\N
12	3	1	2026-05-30 17:43:25	H1DPixJbf7J3k6wvrXtYrM6xQocvhZukp47dPjfrHpGvP3p1IjTql6wnxbQGlcA9	2026-06-05 00:00:00	\N	\N
13	3	4	2026-05-30 17:43:25	7PSq2mPu5F5RKqI1mlrY2vu8OBU9hqUNbZ6kEoo1PhB2MR4TIhNC3XjPuYL9XxUU	2026-06-05 00:00:00	\N	\N
\.


--
-- Data for Name: rfqs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.rfqs (id, request_id, operator_id, title, description, service_type, deadline_at, status, created_at, updated_at) FROM stdin;
2	2	1	Баку, Шеки, Масаллы — Transport	Просьба отправлять предложения во время	transport	2026-06-02	awaiting	2026-05-30 17:43:25	2026-05-30 21:54:00
3	2	1	Баку, Шеки, Масаллы — Guide	Просьба отправлять предложения во время	guide	2026-06-02	awaiting	2026-05-30 17:43:25	2026-05-30 22:27:29
1	2	1	Баку, Шеки, Масаллы — Accommodation	Просьба отправлять предложения во время	accommodation	2026-06-02	awaiting	2026-05-30 17:43:25	2026-05-31 12:48:30
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
hoWbN6DYI4t4ERvLaEtrqf6VqThQ8EdnXLwDVbgC	9	172.22.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:151.0) Gecko/20100101 Firefox/151.0	eyJfdG9rZW4iOiJ4d3Z2allZQmI1WmI5WXVBb1pINU1LM001bVN5VUVSMER3QVlIaWpoIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwODBcL3N1cHBsaWVyXC9yZnFzXC9jb21wb3NlP3JlcXVlc3RfaWQ9MiJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwODBcL2FwaVwvc3VwcGxpZXJzXC80XC9zZXJ2aWNlcz9wZXJfcGFnZT0yMDAiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjksInBhc3N3b3JkX2hhc2hfd2ViIjoiZmQyZjAyYTRlZWZiOGY4NTViNzE5YjczYTFmNmQ5NzliMDY3MDdkNWQ5NjJmNmY2NzI5ODg4YjUxZDYxZmM4NiJ9	1780254980
vxhdotFbQxYSdvEcKyRdc4ZvLuWkSEoczACpplLk	1	172.22.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJLQmRUU3FWbFpuYmJTNGtTQW1TVTJLdFJuWlRZQjhaMjc0bTZaQ05ZIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwODBcL2FkbWluXC9vZmZlcnNcLzEwMDQifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgwXC9hcGlcL3N1cHBsaWVycyIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF93ZWIiOiI4MDZhMTAyM2FhNzBkZDg1MTg5NzkwNWY2Y2VlNmFlOTczNTY1ODFmN2ZlNWVlNTIyZmFmNDMwODM4OTljNjhjIn0=	1780254112
ry4YGjeiTITSN3oymEiRWaMRjPeAi3Oi3vCwI0dZ	2	172.22.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJKeUJMVTY1TXBFenF2Z3dYMUZvWHU1N010elRNbFNpUmlnS2g3NlVoIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwODBcL2FnZW5jeVwvZGFzaGJvYXJkIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODA4MFwvYXBpXC9hdHRhY2htZW50c1wvMlwvZG93bmxvYWQiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjIsInBhc3N3b3JkX2hhc2hfd2ViIjoiMDI3MjI5NDA4YWNjM2VmODY4ZmFlOGMyOTI1MDllODRmYWI5ZDJiYjZkZjlkYTZiN2MwMmJmOTMyZTRjN2ZhZiJ9	1780254975
esSj1DbqE5ktprp2o8ox8fjCnQrgVsjiXJfGf1Tu	1	172.22.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJwSVlPT0lNVVlXT0pNd2QybmFDVEFZMG00OW02VHJnbUVYZ3A2T2JtIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwODBcL2FkbWluXC9zdXBwbGllcnMifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgwXC9hcGlcL2FnZW5jaWVzIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6IjgwNmExMDIzYWE3MGRkODUxODk3OTA1ZjZjZWU2YWU5NzM1NjU4MWY3ZmU1ZWU1MjJmYWY0MzA4Mzg5OWM2OGMifQ==	1780288189
\.


--
-- Data for Name: supplier_incidents; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.supplier_incidents (id, supplier_id, type, severity, subject_type, subject_id, context, notes, created_at, updated_at) FROM stdin;
1	4	offer_withdrawn	low	offer	1002	{"from_status":"received","rfq_id":3}	\N	2026-05-30 22:27:33	2026-05-30 22:27:33
2	4	offer_withdrawn	low	offer	1004	{"from_status":"received","rfq_id":1}	\N	2026-05-31 16:10:30	2026-05-31 16:10:30
\.


--
-- Data for Name: supplier_profiles; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.supplier_profiles (id, user_id, service_types, description, website, is_active, created_at, updated_at) FROM stdin;
1	6	["accommodation"]	Пятизвёздочный отель в знаменитых Пламенных башнях с панорамным видом на Каспийское море и Баку.	https://www.fairmont.com/baku	t	2026-05-22 08:54:55	2026-05-22 08:54:55
2	7	["accommodation"]	Бутик-отель 5★ в сердце Старого города (Ичери-шехер). Интерьеры в стиле ханской эпохи, ресторан азербайджанской кухни.	https://www.shahpalacehotel.az	t	2026-05-22 08:54:55	2026-05-22 08:54:55
3	8	["accommodation"]	Горный курорт 4★ в Габале у подножия Кавказского хребта. Специализация — групповые и корпоративные туры.	https://www.qafqazresort.az	t	2026-05-22 08:54:55	2026-05-22 08:54:55
5	10	["transport"]	Трансферы аэропорт Гейдар Алиев ↔ Баку и экскурсионные поездки по Абшеронскому полуострову и Гобустану.	https://www.caspianshuttle.az	t	2026-05-22 08:54:55	2026-05-22 08:54:55
6	11	["guide", "activity"]	Лицензированные гиды по Баку и Азербайджану. Русский, казахский, узбекский, английский языки. Специализация — групповые туры из СНГ.	https://www.bakuguide.az	t	2026-05-22 08:54:55	2026-05-22 08:54:55
7	12	["guide", "activity"]	Экскурсии и приключенческие туры по регионам Азербайджана. Шеки, Лагич, Куба, Гянджа. Русский и узбекский языки.	https://www.caucasusexplore.az	t	2026-05-22 08:54:56	2026-05-22 08:54:56
4	9	["transport"]	Трансферы и туристические перевозки по всему Азербайджану. Флот от минибасов до туристических автобусов на 50 мест.	https://www.bakutransfer.az	t	2026-05-22 08:54:55	2026-05-25 06:22:32
\.


--
-- Data for Name: supplier_services; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.supplier_services (id, supplier_id, type, name, description, capacity, base_price, currency, price_unit, is_available, created_at, updated_at, contact_name, contact_phone) FROM stdin;
2	1	accommodation	Signature Suite	Фирменный люкс с панорамным видом на город и Пламенные башни. Персональный дворецкий, 85 кв.м.	2	590.00	USD	per_night	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
4	2	accommodation	Superior Room Old City	Номер Superior с видом на средневековую крепостную стену Ичери-шехер. Включён завтрак.	2	280.00	USD	per_night	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
1	1	accommodation	Deluxe Room Caspian View	Делюкс-номер с видом на Каспийское море. Завтрак включён. 45 кв.м.	2	310.00	USD	per_night	t	2026-05-22 08:54:56	2026-05-26 17:42:09	\N	\N
5	2	accommodation	Khan Suite	Ханский люкс с гостиной в восточном стиле, видом на Девичью башню. Два санузла, 75 кв.м.	2	480.00	USD	per_night	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
6	2	other	Групповой ужин с азербайджанской кухней	Тематический ужин для групп от 8 человек. Плов, шашлык, пахлава. Живая музыка по запросу.	\N	45.00	USD	per_person	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
7	3	accommodation	Standard Room (Mountain View)	Стандартный номер с видом на горы. Завтрак включён. Бассейн и SPA доступны.	2	160.00	USD	per_night	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
8	3	accommodation	Group Rate -20% (15+ pax)	Групповой тариф для 15 и более гостей. Скидка 20% от базовой цены. Завтрак и ужин включены (HB).	2	130.00	USD	per_night	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
9	3	accommodation	Family Suite	Семейный люкс 4 места, две спальни. Балкон с видом на горный хребет.	4	220.00	USD	per_night	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
11	4	transport	Туристический автобус 50 мест	Комфортабельный туристический автобус для больших групп. Кондиционер, откидные кресла, микрофон для гида.	50	350.00	USD	per_day	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
13	4	transport	Airport Transfer GYD	Трансфер аэропорт Гейдар Алиев ↔ Центр Баку. Встреча с табличкой в зале прилёта.	3	40.00	USD	fixed	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
14	5	transport	Ford Transit 16 pax	Минибус Ford Transit на 16 мест. Кондиционер, удобные сиденья, опытный водитель.	16	160.00	USD	per_day	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
15	5	transport	Тур-трансфер Абшерон + Гобустан	Однодневный маршрут: Баку → Гобустан (наскальные рисунки и грязевые вулканы) → Абшерон → Баку.	16	200.00	USD	per_day	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
16	5	transport	Airport Transfer GYD (группа)	Групповой трансфер аэропорт Гейдар Алиев ↔ Баку на минибусе. Встреча с табличкой.	16	90.00	USD	fixed	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
17	6	guide	Русскоязычный гид, Баку	Лицензированный гид по Баку на русском языке. Старый город, Приморский бульвар, Ичери-шехер, музеи.	\N	150.00	USD	per_day	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
18	6	guide	Казахоязычный гид, Баку	Гид, свободно владеющий казахским языком. Экскурсии по историческому центру, мечетям, музею ковра.	\N	180.00	USD	per_day	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
19	6	activity	Групповая экскурсия по Баку (Старый город)	Экскурсия по Ичери-шехер: Девичья башня, Дворец Ширваншахов, мечети, базары. Продолжительность ~5 часов.	30	700.00	USD	per_group	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
20	6	activity	Тур Гобустан + грязевые вулканы (группа)	Однодневная экскурсия: наскальные рисунки ЮНЕСКО в Гобустане + действующие грязевые вулканы. ~6 часов.	25	850.00	USD	per_group	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
21	7	guide	Узбекоязычный гид, Шеки и Лагич	Гид, говорящий на узбекском языке. Шеки — ханский дворец, каравансарай; Лагич — медное ремесло.	\N	170.00	USD	per_day	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
22	7	guide	Русскоязычный гид, регионы Азербайджана	Гид по регионам: Шеки, Куба, Гянджа, Загатала. Специализация — история и традиционная культура.	\N	160.00	USD	per_day	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
23	7	activity	Шеки + Лагич — 2-дневный тур (группа)	Двухдневный маршрут: Шеки (ханский дворец, витражи шебеке, шёлк) + Лагич (медные мастерские). Без проживания.	25	1100.00	USD	per_group	t	2026-05-22 08:54:56	2026-05-22 08:54:56	\N	\N
25	4	accommodation	Домик в лесу	Одноэтажный дом в лесу	5	40.00	AZN	per_day	t	2026-05-31 12:47:41	2026-05-31 12:47:41	Таир	+994555555555
12	4	transport	Mercedes S-Class (VIP)	Представительский автомобиль для VIP-гостей. Профессиональный водитель, встреча в аэропорту.	3	130.00	AZN	per_day	f	2026-05-22 08:54:56	2026-05-29 16:31:13	\N	\N
24	4	guide	Ramin (RU/EN)	Хороший гид по истории	\N	60.00	AZN	per_group	t	2026-05-29 16:51:54	2026-05-29 16:51:54	Рамин	+9945555555
10	4	transport	Mercedes Sprinter 19 pax	Минибус Mercedes Sprinter на 19 мест. Кондиционер, Wi-Fi, профессиональный водитель.	19	190.00	USD	per_day	t	2026-05-22 08:54:56	2026-05-29 16:52:17	Сеймур	+994556666666
\.


--
-- Data for Name: supplier_users; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.supplier_users (id, supplier_id, user_id, role, created_at, updated_at) FROM stdin;
1	1	6	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
2	2	7	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
3	3	8	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
5	5	10	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
6	6	11	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
7	7	12	owner	2026-05-26 08:13:49	2026-05-26 08:13:49
8	8	14	owner	2026-05-26 17:14:02	2026-05-26 17:14:02
9	4	9	owner	2026-05-28 20:36:26	2026-05-28 20:36:26
\.


--
-- Data for Name: suppliers; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.suppliers (id, name, email, phone, country, currency_code, service_types, description, website, is_active, created_at, updated_at, uses_portal, deleted_at) FROM stdin;
3	Qafqaz Resort Hotel	sales@qafqazresort.az	+994 22 256 00 01	\N	AZN	["accommodation"]	Горный курорт 4★ в Габале у подножия Кавказского хребта. Специализация — групповые и корпоративные туры.	https://www.qafqazresort.az	t	2026-05-22 08:54:55	2026-05-22 08:54:55	f	\N
5	Caspian Shuttle MMC	booking@caspianshuttle.az	+994 51 300 77 88	\N	AZN	["transport"]	Трансферы аэропорт Гейдар Алиев ↔ Баку и экскурсионные поездки по Абшеронскому полуострову и Гобустану.	https://www.caspianshuttle.az	t	2026-05-22 08:54:55	2026-05-22 08:54:55	f	\N
6	Baku Guide Service MMC	info@bakuguide.az	+994 55 411 22 33	\N	AZN	["guide", "activity"]	Лицензированные гиды по Баку и Азербайджану. Русский, казахский, узбекский, английский языки. Специализация — групповые туры из СНГ.	https://www.bakuguide.az	t	2026-05-22 08:54:55	2026-05-22 08:54:55	f	\N
7	Caucasus Explore Tours	hello@caucasusexplore.az	+994 55 533 44 11	\N	AZN	["guide", "activity"]	Экскурсии и приключенческие туры по регионам Азербайджана. Шеки, Лагич, Куба, Гянджа. Русский и узбекский языки.	https://www.caucasusexplore.az	t	2026-05-22 08:54:56	2026-05-22 08:54:56	f	\N
8	Поставщик без портала	fff@fff.com	+994504801517	\N	AZN	["transport", "guide"]	\N	\N	t	2026-05-26 17:14:02	2026-05-26 17:14:02	f	\N
1	Fairmont Baku	groups@fairmont-baku.az	+994 12 565 88 88	\N	AZN	["accommodation", "transport", "guide"]	Пятизвёздочный отель в знаменитых Пламенных башнях с панорамным видом на Каспийское море и Баку.	https://www.fairmont.com/baku	t	2026-05-22 08:54:55	2026-05-26 10:50:05	t	\N
4	Baku City Transfer MMC	orders@bakutransfer.az	+994 50 222 33 44	\N	AZN	["accommodation", "transport", "guide"]	Трансферы и туристические перевозки по всему Азербайджану. Флот от минибасов до туристических автобусов на 50 мест.	https://www.bakutransfer.az	t	2026-05-22 08:54:55	2026-05-29 16:28:40	t	\N
2	Shah Palace Hotel	reservations@shahpalace.az	+994 12 493 77 55	\N	AZN	["accommodation"]	Бутик-отель 5★ в сердце Старого города (Ичери-шехер). Интерьеры в стиле ханской эпохи, ресторан азербайджанской кухни.	https://www.shahpalacehotel.az	t	2026-05-22 08:54:55	2026-05-29 16:37:51	t	\N
\.


--
-- Data for Name: travel_request_client; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.travel_request_client (id, travel_request_id, client_id, is_lead) FROM stdin;
\.


--
-- Data for Name: travel_requests; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.travel_requests (id, agency_id, title, destination, travel_date_from, travel_date_to, pax_count, services_needed, notes, status, pax_count_changed_at, created_at, updated_at, deadline_at) FROM stdin;
1	1	Тур в Баку	Баку	2026-07-01	2026-07-05	5	["transport"]	\N	cancelled	\N	2026-05-30 09:03:25	2026-05-30 09:19:47	2026-06-10 00:00:00+00
2	1	Гастрономический тур по Азербайджану	Баку, Шеки, Масаллы	2026-06-05	2026-07-06	10	["accommodation", "transport", "guide"]	Нужен рускоговорящий гид. Просьба организовать в транспорте детское кресло	processing	\N	2026-05-30 10:50:33	2026-05-30 17:43:25	2026-06-02 00:00:00+00
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, role, company_name, phone, country, currency_code) FROM stdin;
6	Fairmont Baku Flame Towers	groups@fairmont-baku.az	\N	$2y$12$qam6EBkS8XjslPnw3N36UOZDWnK7VZ9VKKZVdkjP1ElVUncEAY52G	\N	2026-05-22 08:54:55	2026-05-22 08:54:55	supplier	Fairmont Baku	+994 12 565 88 88	\N	AZN
7	Shah Palace Hotel Baku	reservations@shahpalace.az	\N	$2y$12$ke356pnbpwzorWyzcgXIgOYFbfQxbUzsmScMjhY33x8k.34NRV5qO	\N	2026-05-22 08:54:55	2026-05-22 08:54:55	supplier	Shah Palace Hotel	+994 12 493 77 55	\N	AZN
8	Qafqaz Resort Gabala	sales@qafqazresort.az	\N	$2y$12$Rob2KD4.0WpTuMKO8enOL.EJ9rnEEDs07rxQ3tVuWQK2IT5KnSH8i	\N	2026-05-22 08:54:55	2026-05-22 08:54:55	supplier	Qafqaz Resort Hotel	+994 22 256 00 01	\N	AZN
9	Baku City Transfer	orders@bakutransfer.az	\N	$2y$12$iQVzR/AdIhn47FAKqarCguMtKjjGCsJ9fm3VEhLDYKOH4661cZLZO	\N	2026-05-22 08:54:55	2026-05-22 08:54:55	supplier	Baku City Transfer MMC	+994 50 222 33 44	\N	AZN
10	Caspian Shuttle	booking@caspianshuttle.az	\N	$2y$12$HN1UI2LwA0ko.fWbxysz6.2VMlN5o3Ho/f4FPDM9SOtBcBnU9hHEO	\N	2026-05-22 08:54:55	2026-05-22 08:54:55	supplier	Caspian Shuttle MMC	+994 51 300 77 88	\N	AZN
11	Baku Guide Service	info@bakuguide.az	\N	$2y$12$CZQC5DUFAFqOceu7ZZsA2ePJRTu2nwYzdQSmYpKnYwcvgaQ8PgUN2	\N	2026-05-22 08:54:55	2026-05-22 08:54:55	supplier	Baku Guide Service MMC	+994 55 411 22 33	\N	AZN
12	Caucasus Explore	hello@caucasusexplore.az	\N	$2y$12$K88AFGQHyrZGJPUfQru1.ewqptI4WEV6JO6SgQDgKIc/KwBuZENZm	\N	2026-05-22 08:54:56	2026-05-22 08:54:56	supplier	Caucasus Explore Tours	+994 55 533 44 11	\N	AZN
1	Ruslan	dev@caspirex.com	\N	$2y$12$XL/eXVDZvhnJUyhWPHSkcOS79yP490CxciupDI44Hmpv6r1iJfgkW	\N	2026-05-22 08:54:54	2026-05-22 08:54:54	operator	Azerbaijan Tours DMC	+994 12 498 55 00	\N	AZN
2	Nomad Travel Almaty	groups@nomadtravel.kz	\N	$2y$12$TTFyh6qH7Ss3WRyRULi.N.td5BAS1PJgmoQUH/mY3qec9MnPl9/gS	\N	2026-05-22 08:54:54	2026-05-22 10:17:45	agency	Nomad Travel LLP	+7 727 344 55 66	KZ	AZN
3	AsiaTours Kazakhstan	booking@asiatours.kz	\N	$2y$12$kecRvRVObz.CosnDFHEcv.lIZ.lECPe7iE/PsdkG9tCNaPgN1RIFO	\N	2026-05-22 08:54:54	2026-05-22 10:17:45	agency	AsiaTours Kazakhstan ТОО	+7 717 272 10 20	KZ	AZN
4	Samarkand Express Travel	info@samarkandexpress.uz	\N	$2y$12$xY.RynBa7AQpH3IPdn0d4.dL6mUevKyF8vhWBcLlxe4NDrS/KpFa2	\N	2026-05-22 08:54:54	2026-05-22 10:17:45	agency	Samarkand Express Travel MChJ	+998 66 234 56 78	UZ	AZN
5	Fergana Tourist Group	tours@ferganatg.uz	\N	$2y$12$6R8NNU4ib2J4rqbRw/AB3OqcWRle5e/EVyKOaIvFhcaSP2lXqBH3y	\N	2026-05-22 08:54:54	2026-05-22 10:17:45	agency	Fergana Tourist Group MChJ	+998 73 244 11 22	UZ	AZN
13	ghjk	ghjk@box.az	\N	$2y$12$Oe0f4aH3cgm9j3CXaCxXruUc2ZWvLsCNWzZxrosShlpuuNb22VyZm	\N	2026-05-26 09:28:32	2026-05-26 09:28:32	agency	\N	\N	\N	AZN
14	Поставщик без портала	fff@fff.com	\N	$2y$12$AoJfrC0jZJkENJyDxh3kYOWk31WROBmM.dVN0uUDagGACIQ4EFl5q	\N	2026-05-26 17:14:02	2026-05-26 17:14:02	supplier	\N	\N	\N	AZN
15	Ffff	fff@fff.ru	\N	$2y$12$ACx.t/FPPOv6zpmqwudcZOnBJWx46tKRFXWaqxpL9uyvQaE4wyDna	\N	2026-05-28 05:18:32	2026-05-28 05:18:32	agency	\N	\N	\N	AZN
16	dfdf	dfdf@dfd.ru	\N	$2y$12$8JEQjeftPVOBYEQrAPAMNuoGEiH5rjsb5KcILFiZKpAg1rkIwbGXq	\N	2026-06-01 04:29:49	2026-06-01 04:29:49	agency	\N	\N	\N	AZN
\.


--
-- Name: agencies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.agencies_id_seq', 5, true);


--
-- Name: agency_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.agency_users_id_seq', 7, true);


--
-- Name: attachments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.attachments_id_seq', 10, true);


--
-- Name: bookings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.bookings_id_seq', 1, false);


--
-- Name: clients_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.clients_id_seq', 7, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: markup_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.markup_settings_id_seq', 5, true);


--
-- Name: media_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.media_id_seq', 18, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.migrations_id_seq', 57, true);


--
-- Name: offer_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.offer_items_id_seq', 2, true);


--
-- Name: offers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.offers_id_seq', 1006, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 66, true);


--
-- Name: proposal_offer_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.proposal_offer_id_seq', 3, true);


--
-- Name: proposals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.proposals_id_seq', 1002, true);


--
-- Name: rfq_supplier_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.rfq_supplier_id_seq', 13, true);


--
-- Name: rfqs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.rfqs_id_seq', 1001, false);


--
-- Name: supplier_incidents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.supplier_incidents_id_seq', 2, true);


--
-- Name: supplier_profiles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.supplier_profiles_id_seq', 7, true);


--
-- Name: supplier_services_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.supplier_services_id_seq', 26, true);


--
-- Name: supplier_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.supplier_users_id_seq', 9, true);


--
-- Name: suppliers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.suppliers_id_seq', 8, true);


--
-- Name: travel_request_client_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.travel_request_client_id_seq', 1, false);


--
-- Name: travel_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.travel_requests_id_seq', 1001, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.users_id_seq', 16, true);


--
-- Name: agencies agencies_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.agencies
    ADD CONSTRAINT agencies_pkey PRIMARY KEY (id);


--
-- Name: agency_users agency_users_agency_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.agency_users
    ADD CONSTRAINT agency_users_agency_id_user_id_unique UNIQUE (agency_id, user_id);


--
-- Name: agency_users agency_users_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.agency_users
    ADD CONSTRAINT agency_users_pkey PRIMARY KEY (id);


--
-- Name: attachments attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_pkey PRIMARY KEY (id);


--
-- Name: bookings bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: clients clients_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_pkey PRIMARY KEY (id);


--
-- Name: currencies currencies_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.currencies
    ADD CONSTRAINT currencies_pkey PRIMARY KEY (code);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: markup_settings markup_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.markup_settings
    ADD CONSTRAINT markup_settings_pkey PRIMARY KEY (id);


--
-- Name: markup_settings markup_settings_service_type_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.markup_settings
    ADD CONSTRAINT markup_settings_service_type_unique UNIQUE (service_type);


--
-- Name: media media_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_pkey PRIMARY KEY (id);


--
-- Name: media media_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_uuid_unique UNIQUE (uuid);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: offer_items offer_items_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.offer_items
    ADD CONSTRAINT offer_items_pkey PRIMARY KEY (id);


--
-- Name: offers offers_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.offers
    ADD CONSTRAINT offers_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: proposal_offer proposal_offer_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposal_offer
    ADD CONSTRAINT proposal_offer_pkey PRIMARY KEY (id);


--
-- Name: proposal_offer proposal_offer_proposal_id_offer_id_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposal_offer
    ADD CONSTRAINT proposal_offer_proposal_id_offer_id_unique UNIQUE (proposal_id, offer_id);


--
-- Name: proposals proposals_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposals
    ADD CONSTRAINT proposals_pkey PRIMARY KEY (id);


--
-- Name: rfq_shared_attachments rfq_shared_attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_shared_attachments
    ADD CONSTRAINT rfq_shared_attachments_pkey PRIMARY KEY (rfq_id, attachment_id);


--
-- Name: rfq_supplier rfq_supplier_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_supplier
    ADD CONSTRAINT rfq_supplier_pkey PRIMARY KEY (id);


--
-- Name: rfq_supplier rfq_supplier_rfq_id_supplier_id_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_supplier
    ADD CONSTRAINT rfq_supplier_rfq_id_supplier_id_unique UNIQUE (rfq_id, supplier_id);


--
-- Name: rfq_supplier rfq_supplier_token_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_supplier
    ADD CONSTRAINT rfq_supplier_token_unique UNIQUE (token);


--
-- Name: rfqs rfqs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfqs
    ADD CONSTRAINT rfqs_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: supplier_incidents supplier_incidents_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_incidents
    ADD CONSTRAINT supplier_incidents_pkey PRIMARY KEY (id);


--
-- Name: supplier_profiles supplier_profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_profiles
    ADD CONSTRAINT supplier_profiles_pkey PRIMARY KEY (id);


--
-- Name: supplier_profiles supplier_profiles_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_profiles
    ADD CONSTRAINT supplier_profiles_user_id_unique UNIQUE (user_id);


--
-- Name: supplier_services supplier_services_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_services
    ADD CONSTRAINT supplier_services_pkey PRIMARY KEY (id);


--
-- Name: supplier_users supplier_users_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_users
    ADD CONSTRAINT supplier_users_pkey PRIMARY KEY (id);


--
-- Name: supplier_users supplier_users_supplier_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_users
    ADD CONSTRAINT supplier_users_supplier_id_user_id_unique UNIQUE (supplier_id, user_id);


--
-- Name: suppliers suppliers_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_pkey PRIMARY KEY (id);


--
-- Name: travel_request_client travel_request_client_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.travel_request_client
    ADD CONSTRAINT travel_request_client_pkey PRIMARY KEY (id);


--
-- Name: travel_request_client travel_request_client_travel_request_id_client_id_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.travel_request_client
    ADD CONSTRAINT travel_request_client_travel_request_id_client_id_unique UNIQUE (travel_request_id, client_id);


--
-- Name: travel_requests travel_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.travel_requests
    ADD CONSTRAINT travel_requests_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: attachments_attachable_type_attachable_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX attachments_attachable_type_attachable_id_index ON public.attachments USING btree (attachable_type, attachable_id);


--
-- Name: bookings_agency_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX bookings_agency_id_index ON public.bookings USING btree (agency_id);


--
-- Name: bookings_operator_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX bookings_operator_id_index ON public.bookings USING btree (operator_id);


--
-- Name: bookings_proposal_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX bookings_proposal_id_index ON public.bookings USING btree (proposal_id);


--
-- Name: bookings_request_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX bookings_request_id_index ON public.bookings USING btree (request_id);


--
-- Name: bookings_status_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX bookings_status_index ON public.bookings USING btree (status);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: clients_agency_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX clients_agency_id_index ON public.clients USING btree (agency_id);


--
-- Name: currencies_is_active_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX currencies_is_active_index ON public.currencies USING btree (is_active);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: media_model_type_model_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX media_model_type_model_id_index ON public.media USING btree (model_type, model_id);


--
-- Name: media_order_column_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX media_order_column_index ON public.media USING btree (order_column);


--
-- Name: offer_items_offer_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX offer_items_offer_id_index ON public.offer_items USING btree (offer_id);


--
-- Name: offers_rfq_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX offers_rfq_id_index ON public.offers USING btree (rfq_id);


--
-- Name: offers_status_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX offers_status_index ON public.offers USING btree (status);


--
-- Name: offers_supplier_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX offers_supplier_id_index ON public.offers USING btree (supplier_id);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: proposal_offer_proposal_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX proposal_offer_proposal_id_index ON public.proposal_offer USING btree (proposal_id);


--
-- Name: proposals_operator_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX proposals_operator_id_index ON public.proposals USING btree (operator_id);


--
-- Name: proposals_request_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX proposals_request_id_index ON public.proposals USING btree (request_id);


--
-- Name: proposals_status_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX proposals_status_index ON public.proposals USING btree (status);


--
-- Name: rfq_supplier_rfq_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX rfq_supplier_rfq_id_index ON public.rfq_supplier USING btree (rfq_id);


--
-- Name: rfqs_operator_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX rfqs_operator_id_index ON public.rfqs USING btree (operator_id);


--
-- Name: rfqs_request_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX rfqs_request_id_index ON public.rfqs USING btree (request_id);


--
-- Name: rfqs_status_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX rfqs_status_index ON public.rfqs USING btree (status);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: supplier_incidents_subject_type_subject_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX supplier_incidents_subject_type_subject_id_index ON public.supplier_incidents USING btree (subject_type, subject_id);


--
-- Name: supplier_incidents_supplier_id_severity_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX supplier_incidents_supplier_id_severity_index ON public.supplier_incidents USING btree (supplier_id, severity);


--
-- Name: supplier_incidents_supplier_id_type_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX supplier_incidents_supplier_id_type_index ON public.supplier_incidents USING btree (supplier_id, type);


--
-- Name: supplier_profiles_is_active_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX supplier_profiles_is_active_index ON public.supplier_profiles USING btree (is_active);


--
-- Name: supplier_services_supplier_id_is_available_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX supplier_services_supplier_id_is_available_index ON public.supplier_services USING btree (supplier_id, is_available);


--
-- Name: supplier_services_supplier_id_type_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX supplier_services_supplier_id_type_index ON public.supplier_services USING btree (supplier_id, type);


--
-- Name: travel_request_client_travel_request_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX travel_request_client_travel_request_id_index ON public.travel_request_client USING btree (travel_request_id);


--
-- Name: travel_requests_agency_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX travel_requests_agency_id_index ON public.travel_requests USING btree (agency_id);


--
-- Name: travel_requests_status_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX travel_requests_status_index ON public.travel_requests USING btree (status);


--
-- Name: agency_users agency_users_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.agency_users
    ADD CONSTRAINT agency_users_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id) ON DELETE CASCADE;


--
-- Name: agency_users agency_users_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.agency_users
    ADD CONSTRAINT agency_users_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: attachments attachments_uploader_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_uploader_id_foreign FOREIGN KEY (uploader_id) REFERENCES public.users(id);


--
-- Name: bookings bookings_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id);


--
-- Name: bookings bookings_operator_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_operator_id_foreign FOREIGN KEY (operator_id) REFERENCES public.users(id);


--
-- Name: bookings bookings_proposal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_proposal_id_foreign FOREIGN KEY (proposal_id) REFERENCES public.proposals(id);


--
-- Name: bookings bookings_request_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_request_id_foreign FOREIGN KEY (request_id) REFERENCES public.travel_requests(id);


--
-- Name: clients clients_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id);


--
-- Name: offer_items offer_items_offer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.offer_items
    ADD CONSTRAINT offer_items_offer_id_foreign FOREIGN KEY (offer_id) REFERENCES public.offers(id) ON DELETE CASCADE;


--
-- Name: offer_items offer_items_supplier_service_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.offer_items
    ADD CONSTRAINT offer_items_supplier_service_id_foreign FOREIGN KEY (supplier_service_id) REFERENCES public.supplier_services(id) ON DELETE SET NULL;


--
-- Name: offers offers_rfq_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.offers
    ADD CONSTRAINT offers_rfq_id_foreign FOREIGN KEY (rfq_id) REFERENCES public.rfqs(id);


--
-- Name: offers offers_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.offers
    ADD CONSTRAINT offers_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id);


--
-- Name: proposal_offer proposal_offer_offer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposal_offer
    ADD CONSTRAINT proposal_offer_offer_id_foreign FOREIGN KEY (offer_id) REFERENCES public.offers(id);


--
-- Name: proposal_offer proposal_offer_proposal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposal_offer
    ADD CONSTRAINT proposal_offer_proposal_id_foreign FOREIGN KEY (proposal_id) REFERENCES public.proposals(id) ON DELETE CASCADE;


--
-- Name: proposals proposals_operator_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposals
    ADD CONSTRAINT proposals_operator_id_foreign FOREIGN KEY (operator_id) REFERENCES public.users(id);


--
-- Name: proposals proposals_request_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.proposals
    ADD CONSTRAINT proposals_request_id_foreign FOREIGN KEY (request_id) REFERENCES public.travel_requests(id);


--
-- Name: rfq_shared_attachments rfq_shared_attachments_attachment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_shared_attachments
    ADD CONSTRAINT rfq_shared_attachments_attachment_id_foreign FOREIGN KEY (attachment_id) REFERENCES public.attachments(id) ON DELETE CASCADE;


--
-- Name: rfq_shared_attachments rfq_shared_attachments_rfq_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_shared_attachments
    ADD CONSTRAINT rfq_shared_attachments_rfq_id_foreign FOREIGN KEY (rfq_id) REFERENCES public.rfqs(id) ON DELETE CASCADE;


--
-- Name: rfq_supplier rfq_supplier_rfq_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_supplier
    ADD CONSTRAINT rfq_supplier_rfq_id_foreign FOREIGN KEY (rfq_id) REFERENCES public.rfqs(id) ON DELETE CASCADE;


--
-- Name: rfq_supplier rfq_supplier_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfq_supplier
    ADD CONSTRAINT rfq_supplier_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id);


--
-- Name: rfqs rfqs_operator_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfqs
    ADD CONSTRAINT rfqs_operator_id_foreign FOREIGN KEY (operator_id) REFERENCES public.users(id);


--
-- Name: rfqs rfqs_request_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.rfqs
    ADD CONSTRAINT rfqs_request_id_foreign FOREIGN KEY (request_id) REFERENCES public.travel_requests(id);


--
-- Name: supplier_incidents supplier_incidents_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_incidents
    ADD CONSTRAINT supplier_incidents_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON DELETE CASCADE;


--
-- Name: supplier_profiles supplier_profiles_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_profiles
    ADD CONSTRAINT supplier_profiles_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: supplier_services supplier_services_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_services
    ADD CONSTRAINT supplier_services_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id);


--
-- Name: supplier_users supplier_users_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_users
    ADD CONSTRAINT supplier_users_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON DELETE CASCADE;


--
-- Name: supplier_users supplier_users_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.supplier_users
    ADD CONSTRAINT supplier_users_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: travel_request_client travel_request_client_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.travel_request_client
    ADD CONSTRAINT travel_request_client_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: travel_request_client travel_request_client_travel_request_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.travel_request_client
    ADD CONSTRAINT travel_request_client_travel_request_id_foreign FOREIGN KEY (travel_request_id) REFERENCES public.travel_requests(id) ON DELETE CASCADE;


--
-- Name: travel_requests travel_requests_agency_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.travel_requests
    ADD CONSTRAINT travel_requests_agency_id_foreign FOREIGN KEY (agency_id) REFERENCES public.agencies(id);


--
-- PostgreSQL database dump complete
--

\unrestrict 5v6Jx7faChPIiZDYVQvOmBk1bailGmSPGYmCkJz9AhQZWTYnfK9uZYfsoWnjmzY

