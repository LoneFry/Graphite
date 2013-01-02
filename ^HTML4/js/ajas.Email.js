/******************************************************************************
 * Project     : AJAS
 *                Asynchronus Javascript And Stuff
 * Created By  : LoneFry
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 * Latest Ver  : https://github.com/LoneFry/AJAS
 *
 * The way I want to do emails (Apr 11, 2007) rev. (Apr 20, 2007)
 * If you find an error, or improvement, let me know: dev at ajas dot us
 *
 * ajas.Email.magic()       - call this onblur, scrubs and validates
 * ajas.Email.validate()    - call for true/false validation
 * ajas.Email.parse()       - don't call this
 *
 * TLD list taken from http://data.iana.org/TLD/tlds-alpha-by-domain.txt
 * ref: RFC822,RFC1035,icann.org,iana.org
 *****************************************************************************/
if("undefined" == typeof(ajas))ajas={};
ajas.Email=ajas.Email?ajas.Email:{};


// use this function for form feedback
ajas.Email.magic=function(oInput, bStrict, bStrict2) {
    // Set preferred defaults here.
    // bStrict=true will validate the domain as an internet domain
    if(arguments.length < 2) var bStrict=true;
    // bStrict2=true will validate the TLD against a strict list
    //ONLY SET IF YOU KEEP THAT LIST UP TO DATE!
    if(arguments.length < 3) var bStrict2=false;

    var sLabel = oInput.id + 'Msg';
    oInput.className = oInput.className.replace(/ajas_email_error/g, '');
    try {
        if (oInput.value == '') {
            document.getElementById(sLabel).innerHTML = 'user@domain'+(bStrict?'.tld':'');
            return;
        }
        var sEmail = ajas.Email.parse(oInput.value, bStrict, bStrict2);
        // Human readable email
        document.getElementById(sLabel).innerHTML = oInput.value = sEmail;
    } catch (e) {
        oInput.className += ' ajas_email_error';
        var message = e.message;
        // Fix for IE6 bug
        if (message.indexOf('is null or not an object') > -1) {
            message = 'Invalid Email string';
        }
        document.getElementById(sLabel).innerHTML = message;
    }

};


ajas.Email.parse=function(sEmail, bStrict, bStrict2) {
    if(arguments.length < 2) var bStrict=true;
    if(arguments.length < 3) var bStrict2=false;

    var aParts=/^([^@]+)@([^@]+)$/.exec(sEmail);
    //same as//var aParts=sEmail.match(/^(.+)@(.+)$/);
    if (aParts==null) {
        throw new Error("Wrong number of '@'");
    }
    var sUser=aParts[1];
    var sDomain=aParts[2];

    //NOT:   CTRL     SP  (   )   <   >   @   ,   ;   :   \   "   .   [   ]  DEL EXT
    sAtom='[^\000-\037\040\050\051\074\076\100\054\073\072\134\042\056\133\\]\177\200-\377]+';
    rAtom=new RegExp('^'+sAtom+'$');
    sQuoted='"[\040\041\043-\176]+"';
    rQuoted=new RegExp('^'+sQuoted+'$');
    rWord=new RegExp('^('+sQuoted+'|'+sAtom+')$');
    rLocalPart=new RegExp('^('+sQuoted+'|'+sAtom+')(\\.('+sQuoted+'|'+sAtom+'))*$');

    //test user
    if (sUser.match(rLocalPart) == null) {
        throw new Error("Invalid User part");
    }

    sDomainIP='\\[(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})\\]';
    rDomainIP=new RegExp('^'+sDomainIP+'$');
    rDomainNa=new RegExp('^'+sAtom+'(\\.'+sAtom+')*$');
    rDomain=new RegExp('^('+sDomainIP+'|('+sAtom+'(\\.'+sAtom+')*))$');

    //test domain
    if (sDomain.match(rDomain) == null) {
        throw new Error("Invalid Domain part");
    }

    //if IP, validate IP
    if (aBits=sDomain.match(rDomainIP)) {
        if (aBits[1]>255) throw new Error("Invalid IP address");
        if (aBits[2]>255) throw new Error("Invalid IP address");
        if (aBits[3]>255) throw new Error("Invalid IP address");
        if (aBits[4]>255) throw new Error("Invalid IP address");
    }
    //if bStrict && Named Domain, validate tld & RFC1035
    if (bStrict && sDomain.match(rDomainNa) != null) {
        var aBits=sDomain.split('.');

        if (bStrict2) { //check complete list
            //TLD list taken from http://data.iana.org/TLD/tlds-alpha-by-domain.txt
            //# Version 2011060300, Last Updated Fri Jun  3 14:07:02 2011 UTC
            var rTLD=/^(AC|AD|AE|AERO|AF|AG|AI|AL|AM|AN|AO|AQ|AR|ARPA|AS|ASIA|AT|AU|AW|AX|AZ|BA|BB|BD|BE|BF|BG|BH|BI|BIZ|BJ|BM|BN|BO|BR|BS|BT|BV|BW|BY|BZ|CA|CAT|CC|CD|CF|CG|CH|CI|CK|CL|CM|CN|CO|COM|COOP|CR|CU|CV|CX|CY|CZ|DE|DJ|DK|DM|DO|DZ|EC|EDU|EE|EG|ER|ES|ET|EU|FI|FJ|FK|FM|FO|FR|GA|GB|GD|GE|GF|GG|GH|GI|GL|GM|GN|GOV|GP|GQ|GR|GS|GT|GU|GW|GY|HK|HM|HN|HR|HT|HU|ID|IE|IL|IM|IN|INFO|INT|IO|IQ|IR|IS|IT|JE|JM|JO|JOBS|JP|KE|KG|KH|KI|KM|KN|KP|KR|KW|KY|KZ|LA|LB|LC|LI|LK|LR|LS|LT|LU|LV|LY|MA|MC|MD|ME|MG|MH|MIL|MK|ML|MM|MN|MO|MOBI|MP|MQ|MR|MS|MT|MU|MUSEUM|MV|MW|MX|MY|MZ|NA|NAME|NC|NE|NET|NF|NG|NI|NL|NO|NP|NR|NU|NZ|OM|ORG|PA|PE|PF|PG|PH|PK|PL|PM|PN|PR|PRO|PS|PT|PW|PY|QA|RE|RO|RS|RU|RW|SA|SB|SC|SD|SE|SG|SH|SI|SJ|SK|SL|SM|SN|SO|SR|ST|SU|SV|SY|SZ|TC|TD|TEL|TF|TG|TH|TJ|TK|TL|TM|TN|TO|TP|TR|TRAVEL|TT|TV|TW|TZ|UA|UG|UK|US|UY|UZ|VA|VC|VE|VG|VI|VN|VU|WF|WS|XN--0ZWM56D|XN--11B5BS3A9AJ6G|XN--3E0B707E|XN--45BRJ9C|XN--80AKHBYKNJ4F|XN--90A3AC|XN--9T4B11YI5A|XN--CLCHC0EA0B2G2A9GCD|XN--DEBA0AD|XN--FIQS8S|XN--FIQZ9S|XN--FPCRJ9C3D|XN--FZC2C9E2C|XN--G6W251D|XN--GECRJ9C|XN--H2BRJ9C|XN--HGBK6AJ7F53BBA|XN--HLCJ6AYA9ESC7A|XN--J6W193G|XN--JXALPDLP|XN--KGBECHTV|XN--KPRW13D|XN--KPRY57D|XN--LGBBAT1AD8J|XN--MGBAAM7A8H|XN--MGBAYH7GPA|XN--MGBBH1A71E|XN--MGBC0A9AZCG|XN--MGBERP4A5D4AR|XN--O3CW4H|XN--OGBPF8FL|XN--P1AI|XN--PGBS0DH|XN--S9BRJ9C|XN--WGBH1C|XN--WGBL6A|XN--XKC2AL3HYE2A|XN--XKC2DL3A5EE0H|XN--YFRO4I67O|XN--YGBI2AMMX|XN--ZCKZAH|XXX|YE|YT|ZA|ZM|ZW)$/i;
            if (aBits[aBits.length-1].match(rTLD) == null) {
                throw new Error("Invalid Domain TLD");
            }
        } else { //check short list, and assume a 2 letter TLD is a valid one
            //TLD list taken from http://www.icann.org/registries/listing.html
            var rTLD=/^(aero|asia|arpa|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|post|pro|tel|travel|xxx)$/i;
             if (aBits[aBits.length-1].length!=2 &&           //for country codes
                aBits[aBits.length-1].match(rTLD) == null) { //or standard TLDs
                throw new Error("Invalid Domain TLD");
            }
            if (aBits[aBits.length-1].length<2) { // single character TLD?!
                throw new Error("Invalid Domain TLD");
            }
        }
        if (aBits.length>1 && aBits[aBits.length-2].length<2) { // single character SLD?!
            throw new Error("Invalid Domain");
        }
        for(var i in aBits) {
            // even though RFC1035 says start with a letter, we don't always
            //check for hyphens below to be more specfic in our error
            if (aBits[i].match(/^[a-zA-Z0-9\-]{1,63}$/) == null) {
                throw new Error("Invalid Domain Name");
            }
            if (aBits[i].charAt(0)=='-') {
                throw new Error("Leading Hyphen Error");
            }
            if (aBits[i].charAt(aBits[i].length-1)=='-') {
                throw new Error("Trailing Hyphen Error");
            }
            if (aBits[i].length > 63) {
                throw new Error("Maximum Length Error");
            }
        }
    }

    return sEmail;
};

//use this function to get a quiet true/false response
ajas.Email.validate=function(oInput, bStrict, bStrict2) {
    if(arguments.length < 2) var bStrict=true;
    if(arguments.length < 3) var bStrict2=false;
    try {
        ajas.Email.parse(oInput.value, bStrict, bStrict2);
    } catch (e) {
        return false;
    }
    return true;
};
