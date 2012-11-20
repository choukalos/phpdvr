-- DVR SQL Schema 
-- mysql format
-- database: dvr
-- RDBM setup for schedules direct data + queue + avail channels
create table `stations` (
  `id`               integer(10) NOT NULL,
  `fccChannelNumber` integer(10) NOT NULL,
  `callSign`		 varchar(11) NOT NULL,
  `name`             varchar(40) NOT NULL,
  `affiliate`        varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)	
) ENGINE=MyISM DEFAULT CHARSET=latin1;

-- Lookup table between stations (SD) and channels (hdhomerun scanned)
create table `lineups` (
  `station_id`       integer(10) NOT NULL,
  `channel`			 integer(3)  NOT NULL,
  `channelMinor`	 integer(3)  NOT NULL,
  PRIMARY KEY (`station_id`),
  KEY `station_id` (`station_id`),
  KEY `channel_minor` (`channel`, `channelMinor`)
) ENGINE=MyISM DEFAULT CHARSET=latin1;

-- Scanned HDhomerun channels that I can receive
create table `channels` (
  `device`        varchar(8) not null,
  `tuner`         int not null,
  `band`          varchar(10) not null,
  `freq`          int(10) not null,
  `channel`       int(3) not null,
  `channelMinor`  int(2) not null,
  `callsign`      varchar(8) not null,
  `callsignMinor` varchar(4) not null,
  `fcc_channel`   varchar(8) not null,
  `ss`	          int(3) not null,
  `snq`           int(3) not null,
  `seq`           int(3) not null,
  `use`           int(1) not null,
  `station_id`    int(10) null default null,
  KEY `device_channel_minor` (`device`, `channel`, `channelMinor`),
  KEY `channel_minor` (`channel`, `channelMinor`)
) ENGINE=MyISM DEFAULT CHARSET=latin1;

-- SD data:  Schedules
CREATE TABLE `schedules` (
  `program_id`	   varchar(30) not null,
  `station_id`     integer(10) not null,
  `time`           datetime    not null,
  `duration`       integer(10) not null,
  `tvRating`	   varchar(8)  default null,
  `stereo`         integer(1)  default 0,
  `hdtv`	  	   integer(1)  default 0,
  `closeCaptioned` integer(1) default 0,
  `dolby`		   varchar(8) default null,
  `new`			   integer(1) default 0,  
  `part_number`	   integer(8) default 0,
  `part_total`     integer(8) default 0,
  `record`		   integer(1) default 0,
  KEY `program_id` (`program_id`),
  KEY `station_id` (`station_id`),
  KEY `time` (`time`),
  KEY `record` (`record`)
) ENGINE=MyISM DEFAULT CHARSET=latin1;

-- SD Data:  Programs -> links to program in schedules to tell you what it is youre watching

CREATE TABLE `programs` (
  `id`                       varchar(30) not null,
  `title`                    varchar(80) not null,
  `subtitle`                 varchar (80) not null,
  `description`              text not null,
  `showType`	             char(40) not null,
  `colorCode`				 varchar(8) not null,
  `series`		             char(40) not null,
  `syndicatedEpisodeNumber`  char(40) not null,
  `originalAirDate`          date default null,
  PRIMARY KEY (`id`),
  KEY `title` (`title`)	
) ENGINE=MyISM DEFAULT CHARSET=latin1;

-- SD:  Genre object -> has a array of genres : genre and relevance for each program, how to model?  Important?
-- CREATE TABLE `genre` (
--  `program_id`				varchar(30) not null,
--  `genre_id`				integer(8)  not null,
--  `relevance`				integer(1)  not null,
--  KEY `program_id` (`program_id`),
--  KEY `genre_id`   (`genre_id`)
-- ) ENGINE=MyISM DEFAULT CHARSET=latin1;
--

-- SYS:  recording queue -> use this table to tag what's to be recorded / when / etc ...
CREATE TABLE `recording` (
  `id`           int(11) NOT NULL AUTO_INCREMENT,
  `program_id`   varchar(30) NOT NULL,
  `series`       char(40)    NOT NULL,
  `start_time`   datetime NOT NULL,
  `duration`     int(11)  NOT NULL,
  `filename`     varchar(100) NOT NULL,
  `deviceid`     varchar(8) default NULL,
  `tuner`	     int(1) default NULL,
  `channel`	     int(3) NOT NULL,
  `channelMinor` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `series` (`series`)
) ENGINE=MyISAM AUTO_INCREMENT=287 DEFAULT CHARSET=latin1;

-- SYS:  series passes -> use this table to id series that we'll constantly be queuing up recordings for ...
CREATE TABLE `series_pass` (
  `series` char(40) NOT NULL,
  `title`  varchar(40) NOT NULL,
  PRIMARY KEY (`series`)	
) ENGINE=MyISAM CHARSET=latin1;

-- SYS:  recorded shows table -> use this table to id programs that have been recorded and can be viewed ...
CREATE TABLE `recorded` (
  `program_id`              varchar(30)  NOT NULL,
  `title`                   varchar(40)  NOT NULL,
  `series`                  char(40)     NOT NULL,
  `syndicatedEpisodeNumber` char(40)     NOT NULL,
  `savefilename`			varchar(100) NOT NULL,
  PRIMARY KEY (`program_id`),
  KEY `series` (`series`)
) ENGINE=MyISAM CHARSET=latin1;
-- END of SQL to setup tables/etc...

-- Create Views for easy data access
CREATE VIEW all_stations as
  select
	a.id as `station_id`,
	a.fccChannelNumber,
	a.callSign,
	a.name,
	a.affiliate,
	b.channel,
	b.channelMinor
  from stations a left join lineups b on (a.id = b.station_id)
  order by b.channel asc, b.channelMinor asc;

CREATE VIEW all_channels as
  select
    band,
    freq,
    channel,
    channelMinor,
    callsign,
    fcc_channel,
    station_id,
    substring_index(fcc_channel, '.', 1) as dev_map_channel,
    substring_index(substring_index(fcc_channel,'.',-1),'.',1) as dev_map_channelMinor
  from channels
  group by 
    band,
    freq,
    channel,
    channelMinor,
    callsign,
    fcc_channel,
    station_id,
    substring_index(fcc_channel, '.', 1),
    substring_index(substring_index(fcc_channel,'.',-1),'.',1)
  order by 
    fcc_channel asc;

-- This view is used to show stations that your devices can see.  Note its important to manually map station_id in the channels table for this view
-- to be populated.  There's no reliable automated way of doing this.  Need to have a setup screen to handle this piece of the puzzle.
CREATE VIEW pvr_stations as
  select
    c.station_id,
    c.fccChannelNumber,
    c.callSign,
    c.name,
    c.affiliate,
    c.channel,
    c.channelMinor,
    d.band,
    d.freq,
    d.channel as `device_channel`,
    d.channelMinor as `device_channelMinor`,
    d.callSign as `device_callSign`,
    d.callSignMinor as `device_callSignMinor`,
    d.fcc_channel as `device_fccChannelNumber`
  from
    channels d inner join all_stations c on (d.station_id = c.station_id)
  group by
    c.station_id, 
    c.fccChannelNumber, 
    c.callSign,
    c.name,
    c.affiliate,
    c.channel,
    c.channelMinor,
    d.band,
    d.freq,
    d.channel,
    d.channelMinor,
    d.callSign,
    d.callSignMinor,
    d.fcc_channel;

-- This view is used to populate the tv guide, program_id is used to show detail on a program and ties into 
-- the recording tables to identify if a show is tagged for season or one time recording
CREATE VIEW pvr_schedule as
  select
    a.station_id,
    c.device_fccChannelNumber,
    c.device_channel,
    c.device_channelMinor,
    a.program_id,
    a.time,
    a.duration,
	a.tvRating,
	a.stereo,
	a.hdtv,
	a.closeCaptioned,
	a.dolby,
	a.new,
	a.part_number,
	a.part_total,
	a.record,
    p.title,
    p.subtitle,
    p.description,
    p.showType,
    p.colorCode,
    p.series,
    p.syndicatedEpisodeNumber,
    p.originalAirDate,
    if(r.program_id is not null, "1","0") as recording,
    r.deviceid as recording_device_id,
    r.tuner as recording_tuner,
    r.start_time as recording_start_time,
    r.duration as recording_duration,
    if(s.series is not null, "1", "0") as season_pass
  from 
    ( ((pvr_stations c left join schedules a on (c.station_id = a.station_id)) left join programs p 
       on (a.program_id = p.id)) left join recording r on (p.id = r.program_id))
        left join series_pass s on (p.series = s.series);

/* SQL to update channels with station_id post channel scan - assume a split on fcc_channel maps to lineups channel/channelMinor columns)
update channels inner join all_stations 
  on (substring_index(channels.fcc_channel,'.',1) = all_stations.channel and 
      substring_index(substring_index(channels.fcc_channel,'.',-1),'.',1) = all_stations.channelMinor) set
  channels.station_id = all_stations.station_id  */


