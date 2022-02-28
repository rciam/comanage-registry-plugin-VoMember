create table cm_voms_members
(
    id           serial
        primary key,
    subject      varchar(256) not null,
    issuer       varchar(256) not null,
    vo_id        varchar(256) not null,
    username     varchar(512),
    email        varchar(512),
    first_update timestamp,
    last_update  timestamp,
    fqans        text,
    constraint cm_voms_members_ukey
        unique (subject, issuer, vo_id)
);

alter table cm_voms_members
    owner to cmregistryadmin;

create index cm_voms_members_i1
    on cm_voms_members (subject);

create index cm_voms_members_i2
    on cm_voms_members (issuer);

create index cm_voms_members_i3
    on cm_voms_members (vo_id);

create index cm_voms_members_i5
    on cm_voms_members (email);

grant select on cm_voms_members to cmregistryro;
