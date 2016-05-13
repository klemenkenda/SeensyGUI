 <?PHP echo "Sorry. You can not access to this file.";exit; ?>
##########################################################
# FILE: config.php                     
# AUTHOR: Klemen Kenda
# DESCRIPTION: Config file for RutkaCMS
# DATE: 15/12/2010
# HISTORY:
##########################################################

###############################
# Main configuration settings
###############################
[type: config]
<part: sitedata>
name = RutkaCMS
login = admin
password = konica
lang = si
langshow = si
checkcode = 0c748871a92fc4338e03d13f01abacd3d2028bce
supercheckcode = 338e03d13f01aba28bce
colorscheme = silver
SQLserver = localhost
DBname = seensy
DBlogin = root
DBpassword = 
loadmodul = upload_module.php
<part: colors>

###############################
# PAGES
###############################
[form: pages]
<part: main>
name = Strani
formtype = auto
listorder = id
<field: pa_pid>
name = Father
type = dropdown_list
data = select id,pa_title from pages order by pa_title;
del_key = flat_text
lst_key = single_value_list
<field: pa_title>
name = Naslov
type = text_field
del_key = flat_text
lst_key = simple_text
<field: pa_subtitle>
name = Podnaslov
type = text_field
del_key = flat_text
<field: pa_template>
name = Template
type = text_field
del_key = flat_text
<field: pa_weight>
name = Pozicija
type = text_field
start = 50
comment = Pozicija strani v meniju (na sitemapu).
del_key = flat_text
lst_key = simple_text
<field: pa_uri>
name = URI
type = text_field
del_key = flat_text
comment = Uporabljaj samo male crke angleske abecede in vezaj "-". Nic drugega!
lst_key = simple_text
<field: pa_description>
name = Opis (HTML desc.)
type = text_field
del_key = flat_text
lst_key = simple_text
<field: pa_keywords>
name = Klj. bes. (HTML keyw.)
type = text_field
del_key = flat_text
lst_key = simple_text
<field: pa_content>
name = Vsebina
type = richtextarea
lst_key = yes_or_no
<field: pa_plugin1>
name = Plugin 1
type = text_field
del_key = flat_text
lst_key = simple_text
<field: pa_plugin2>
name = Plugin 2
type = text_field
del_key = flat_text
lst_key = simple_text
<field: pa_plugin3>
name = Plugin 3
type = text_field
del_key = flat_text
lst_key = simple_text

    
###############################
# USERS
###############################
[form: users]
<part: main>
name = Users
formtype = auto
listorder = id
<field: us_name>
name = Real name
type = text_field
del_key = flat_text
lst_key = simple_text
<field: us_image>
name = User image
type = filename_upload
del_key = flat_text
lst_key = simple_text
dir = ../images/users
<field: us_username>
name = Username
type = text_field
del_key = flat_text
lst_key = simple_text
<field: us_password>
name = Password
type = text_field
del_key = flat_text
lst_key = simple_text
comment = Use MD5 encoded password.
    
###############################
# BRANCHES
###############################
[form: branches]
<part: main>
name = Branches
formtype = auto
listorder = id
<field: br_name>
name = Name
type = text_field
del_key = flat_text
lst_key = simple_text
<field: br_pid>
name = Pilot
type = dropdown_list
data = select id,pi_name from pilots order by pi_name;
del_key = flat_text
lst_key = single_value_list
<field: br_weight>
name = Weight
type = text_field
lst_key = simple_text
del_key = flat_text
start = 50
    
###############################
# VISUALIZATIONS
###############################
[form: visualizations]
<part: main>
name = Visualizations
formtype = auto
listorder = id
<field: vi_name>
name = Name
type = text_field
del_key = flat_text
lst_key = simple_text
<field: vi_subtitle>
name = Subtitle
type = text_field
del_key = flat_text
lst_key = simple_text
<field: vi_bid>
name = Pilot / branch
type = dropdown_list
data = SELECT branches.id AS id,CONCAT(pi_name, ' ', br_name) FROM pilots, branches ORDER BY pi_name, br_name;
    # data = select id,pi_name from pilots order by pi_name;
del_key = flat_text
lst_key = single_value_list
<field: vi_config>
name = Config (JSON)
type = textarea
lst_key = yes_or_no
<field: vi_weight>
name = Weight
start = 50
type = text_field
lst_key = simple_text
    
    
###############################
# PILOTS
###############################
[form: pilots]
<part: main>
name = Pilots
formtype = auto
listorder = id
<field: pi_name>
name = Pilot name
type = text_field
del_key = flat_text
lst_key = simple_text
<field: pi_image>
name = Pilot image
type = filename_upload
del_key = flat_text
lst_key = simple_text
dir = ../images/pilots
<field: pi_subtitle>
name = Subtitle
type = text_field
del_key = flat_text
lst_key = simple_text
    

###############################
# PILOTUSERS
###############################
[form: pilotusers]
<part: main>
name = Pilot-Users
formtype = auto
listorder = id
<field: pu_uid>
name = User
type = dropdown_list
data = select id,us_name from users order by us_name;
del_key = flat_text
lst_key = single_value_list
<field: pu_pid>
name = Pilot
type = dropdown_list
data = select id,pi_name from pilots order by pi_name;
del_key = flat_text
lst_key = single_value_list
<field: pu_default>
name = Default pilot
type = checkbox
lst_key = yes_or_no
    
    
###############################
# VARIABLES
###############################
[form: variables]
<part: main>
name = Spremenljivke
formtype = auto
listorder = id
<field: va_name>
name = Ime
type = text_field
del_key = flat_text
lst_key = simple_text
comment = Ime naj bo enolico, naj ne vsebuje sumnikov in presledkov. Uporablja naj se velike crke in podcrtaje za locevanje besed. V besedilu/predlogi se spremenljivko vkljuc s pomocjo %VAR:IME_SPREMENLJIVKE%.
<field: va_value>
name = Vrednost
type = richtextarea
lst_key = yes_or_no
<field: va_value_en>
name = Vrednost (ENG)
type = richtextarea
lst_key = yes_or_no
<field: va_nohtml>
name = Brez HTML
type = checkbox
lst_key = yes_or_no
comment = V primeru obkljukane 'Brez HTML' opcije, se bo morebitna HTML koda, ki se bo v rich-text urejevalniku pripela besedilu samodejno filtrirala, tako da bo ostalo golo besedilo.



###############################
# WEB CACHE
###############################
[form: webcache]
<part: main>
name = Cache (WWW)
formtype = auto
listorder = id
<field: wc_url>
name = URL
type = text_field
del_key = flat_text
lst_key = simple_text
<field: wc_result>
name = Vrednost
type = textarea
lst_key = yes_or_no

###############################
# File management module section
###############################
#[form: moduleact1]
#<part: main>
#menu = File management
#name = Upload datotek
#formtype = action
#act = moduleact1
#[form: moduleact2]
#<part: main>
#menu = File management
#name = Preglej slike
#formtype = action
#act = moduleact2
#[form: moduleact3]
#<part: main>
#menu = File management
#name = Preglej dokumente
#formtype = action
#act = moduleact3

###############################
# LOGS
###############################
[form: redirect11]
<button: main>
menu = Log
name = Dnevnik napak
formtype = redirect
redir = logs/errors.log
[form: redirect12]
<button: main>
menu = Log
name = Varnostni dnevnik
formtype = redirect
redir = logs/security.log
END_CONFIG
