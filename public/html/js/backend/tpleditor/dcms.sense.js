

	top.dcms_selfclosetags = 'cp:extends,cp:include,cp:set,cp:unset,cp:stop,cp:next,cp:app,cp:jstabs,cp:jscalender,cp:editor';



    top.dcms_tags = new Array;
    top.dcms_tags["cp:addDelNewsletterEmail"] = {
        desc: "Dieses Tag erzeugt, schreibt oder entfernt eine Email aus der Newsletter-Empfänger-Liste. Die Empfänger-Listen werden als CSV-Datei gespeichert und können dann beim Versand im Newslettermodul verwendet werden.",
        attributes: {
            path: 2,
            type: {
                csv: 3,
                customer: 3,
                emailonly: 3
            },
            mailingList: 2,
            doubleoptin: {
                "true": 3,
                "false": 3
            },
            expiredoubleoptin: 2,
            mailid: 2,
            adminmailid: 2,
            subject: 2,
            adminsubject: 2,
            adminemail: 2,
            from: 2,
            id: 2,
            fieldGroup: 2,
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };


    top.dcms_tags["cp:author"] = {
        desc: 'Das cp:author-Tag dient dazu, um den Autor der Seite anzuzeigen. Ist das Attribut \'type\' nicht gesetzt, wird der Benutzername angezeigt. Wenn type="name" ist, dann wird der Vor- und Nachname des Benutzers angezeigt. Ist \'type="initials", dann werden die Initialen des Benutzers angezeigt. Ist kein Vor- und Nachname eingetragen, wird immer der Benutzername angezeigt.',
        attributes: {
            type: {
                name: 3,
                initials: 3
            },
            doc: {
                self: 3,
                top: 3
            },
            creator: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:back"] = {
        desc: "Das cp:back-Tag erzeugt ein HTML-Link-Tag, das auf die vorherige cp:listview-Seite verweist. Der gesamte Inhalt zwischen Start- und Endtag wird verlinkt.",
        attributes: {
            "class": 2,
            style: 2,
            xml: {
                "true": 3,
                "false": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };

    top.dcms_tags["cp:block"] = {
        desc: "Mit dem cp:block-Tag kann man erweiterbare Blöcke/Listen erzeugen. Alles, was zwischen Start- und Endtag steht, wird im Bearbeitungsmodus durch einen Klick auf den Plus-Button angehängt, bzw. eingefügt. Dies können beliebiges HTML sowie fast alle cp:tags sein.",
        attributes: {
            name: 2,
            showselect: {
                "true": 3,
                "false": 3
            },
            start: 2,
            limit: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };

    top.dcms_tags["cp:captcha"] = {
        desc: "Dieses Tag dient dazu, ein Bild mit einem Zufallscode zu generieren.",
        attributes: {
            width: 2,
            height: 2,
            maxlength: 2,
            path: 2,
            subset: {
                alphanum: 3,
                alpha: 3,
                num: 3
            },
            skip: 2,
            fontcolor: {
                "#000000": 3,
                "#ffffff": 3,
                "#ff0000": 3,
                "#00ff00": 3,
                "#0000ff": 3,
                "#ffff00": 3,
                "#ff00ff": 3,
                "#00ffff": 3
            },
            fontsize: 2,
            bgcolor: {
                "#ffffff": 3,
                "#cccccc": 3,
                "#888888": 3
            },
            transparent: {
                "false": 3,
                "true": 3
            },
            style: {
                strikeout: 3,
                fullcircle: 3,
                fullrectangle: 3,
                outlinecircle: 3,
                outlinerectangle: 3
            },
            stylecolor: {
                "#cccccc": 3,
                "#ff0000": 3,
                "#00ff00": 3,
                "#0000ff": 3,
                "#00ffff": 3,
                "#ff00ff": 3,
                "#ffff00": 3
            },
            angle: 2,
            align: {
                random: 3,
                center: 3,
                left: 3,
                right: 3
            },
            valign: {
                random: 3,
                top: 3,
                middle: 3,
                bottom: 3
            },
            font: 2,
            fontpath: 2,
            "case": {
                mix: 3,
                upper: 3,
                lower: 3
            },
            type: {
                gif: 3,
                jpg: 3,
                png: 3
            },
            stylenumber: 2,
            alt: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };


    top.dcms_tags["cp:charset"] = {
        desc: 'Das Tag cp:charset generiert eine Meta-Angabe, die bestimmt mit welchem Zeichensatz die fertige Seite angezeigt wird. Für deutsche Seiten wird normalerweise der Zeichensatz "ISO-8859-15" verwendet. Dieser Tag muss innerhalb der <head></head> Tags der HTML-Seite stehen.',
        attributes: {
            defined: {
                "ISO-8859-1": 3,
                "ISO-8859-2": 3,
                "ISO-8859-3": 3,
                "ISO-8859-4": 3,
                "ISO-8859-5": 3,
                "ISO-8859-6": 3,
                "ISO-8859-7": 3,
                "ISO-8859-8": 3,
                "ISO-8859-9": 3,
                "ISO-8859-10": 3,
                "ISO-8859-11": 3,
                "ISO-8859-13": 3,
                "ISO-8859-14": 3,
                "ISO-8859-15": 3,
                "UTF-8": 3,
                "Windows-1251": 3,
                "Windows-1252": 3
            },
            xml: {
                "true": 3,
                "false": 3
            }
        }
    };



    top.dcms_tags["cp:content"] = {
        desc: "<cp:content /> wird nur innerhalb einer Hauptvorlage eingesetzt. Es markiert die Fläche, in die der Inhalt der Detailvorlage innerhalb der Hauptvorlage eingebunden wird.",
        attributes: {
            name: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };


    top.dcms_tags["cp:css"] = {
        desc: "Das cp:css-Tag erzeugt ein HTML-Tag, das auf ein webEdition-internes CSS Stylesheet mit der unten angegebenen ID verweist. Dadurch können Sie Stylesheets in einer separaten Datei definieren.",
        attributes: {
            id: 2,
            rel: {
                stylesheet: 3,
                "alternate stylesheet": 3
            },
            title: 2,
            media: {
                all: 3,
                braille: 3,
                embossed: 3,
                handheld: 3,
                print: 3,
                projection: 3,
                screen: 3,
                speech: 3,
                tty: 3,
                tv: 3
            },
            xml: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };

    top.dcms_tags["cp:dateSelect"] = {
        desc: "Das cp:dateSelect-Tag gibt ein Auswahlfeld für ein Datum zurück, welches im Zusammenhang mit dem Tag cp:processDateSelect in eine Variable als Unix Timestamp eingelesen werden kann.",
        attributes: {
            name: 2,
            "class": 2,
            submitonchange: {
                "true": 3,
                "false": 3
            },
            start: 2,
            end: 2,
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };


    top.dcms_tags["cp:else"] = {
        desc: "Dieses Tag leitet die Alternative ein, wenn die Bedingung eines if-Tags (z. B. <cp:ifEditmode>, <cp:ifNotVar>, <cp:ifNotEmpty>, <cp:ifFieldNotEmpty>, ) nicht zutrifft.",
        attributes: {
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:extends"] = {
        desc: "Dieses Tag importiert das aktuelle Template in das übergeordnete Template. (Voraussetzung: das übergeordnete Template hat die gleichen <block/> Tags wie das aktuelle Template.)",
        attributes: {
            template: {
                "": 3
            }
        }
    };
    top.dcms_tags["cp:elseif"] = {
        desc: "Dieses Tag leitet die Alternative ein, wenn die Bedingung eines if-Tags (z. B. <cp:if>, <cp:elseif>) nicht zutrifft.",
        attributes: {
            condition: {
                ">": 3,
				"!=": 3,
				"==": 3,
				"===": 3,
				"!==": 3,
				">=": 3,
				"<": 3,
				"&&": 3,
				"||": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:icon"] = {
        desc: 'Das cp:icon-Tag erzeugt ein HTML-Tag, das auf ein webEdition internes Icon mit der unten angegebenen ID verweist. Dadurch können Sie ein Icon einbinden, welches beim Bookmarken Ihrer Homepage im Internet Explorer, Mozilla, Safari und Opera angezeigt wird.<br /><br />Bitte beachten Sie: Die Icon Datei sollte den Dateinamen "favicon.ico" haben und möglichst direkt im Document-Root liegen.',
        attributes: {
            id: 2,
            xml: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:if"] = {
        desc: "Dieses Tag dient dazu, den umschlossenen Inhalt nur dann anzuzeigen, wenn es bei einer Listview auch eine vorherige Seite gibt. Gibt es keine vorherige Seite f?r die Listview, dann wird der umschlossene Inhalt nicht angezeigt.",
        attributes: {
            condition: {
                ">": 3,
				"!=": 3,
				"==": 3,
				"===": 3,
				"!==": 3,
				">=": 3,
				"<": 3,
				"&&": 3,
				"||": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };

    top.dcms_tags["cp:img"] = {
        desc: 'Das cp:img-Tag dient dazu, eine Grafik in den Inhalt eines Dokumentes einzubauen. Im Bearbeitungsmodus eines Dokumentes ist unter der Grafik ein Button "edit" sichtbar. Durch Anklicken des Buttons öffnet sich der Dateimanager, aus dem man eine Grafik auswählen oder neu anlegen kann. Wenn die Attribute "width", "height", "border", "hspace", "vspace", "alt" oder "align" gesetzt werden, dann werden diese Einstellungen für die Grafik verwendet, ansonsten gelten die Einstellungen, welche bei der Grafik gemacht wurden. Wenn das Attribut id gesetzt ist, dann wird die Grafik mit dieser ID benutzt, falls noch keine andere Grafik ausgewählt wurde. Das Attribut showimage ermöglicht es, das Bild im Bearbeiten-Modus nicht anzeigen zu lassen. Mit showinputs lassen sich die Eingabefelder für title und alt-text deaktivieren.',
        attributes: {
            name: 2,
            only: {
                width: 3,
                height: 3,
                alt: 3,
                src: 3
            },
            id: 2,
            width: 2,
            height: 2,
            border: 2,
            hspace: 2,
            vspace: 2,
            alt: 2,
            title: 2,
            startid: 2,
            parentid: 2,
            align: {
                left: 3,
                right: 3,
                top: 3,
                bottom: 3,
                absmiddle: 3,
                middle: 3,
                texttop: 3,
                baseline: 3,
                absbottom: 3
            },
            thumbnail: 2,
            showcontrol: {
                "true": 3,
                "false": 3
            },
            showimage: {
                "false": 3
            },
            xml: {
                "true": 3,
                "false": 3
            },
            showinputs: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            sizingrel: 2,
            sizingstyle: {
                none: 3,
                em: 3,
                ex: 3,
                "%": 3,
                px: 3
            },
            sizingbase: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:include"] = {
        desc: 'Mit diesem-Tag können Sie ein webEdition-Dokument oder eine HTML-Seite in die Vorlage einbinden. Dies ist besonders für Navigationen oder Teile, die auf jeder Vorlage gleich sind, zu empfehlen. Wenn Sie mit dem cp:include-Tag arbeiten, brauchen Sie eine Änderung der Navigation nicht in allen Vorlagen ändern, sondern nur im einzubindenden Dokument. Danach brauchen Sie nur einen "rebuild" auszuführen, und alle Seiten werden automatisch geändert. Haben Sie nur dynamisch erzeugte Seiten, kann der "rebuild" entfallen. Anstelle des cp:include Tags wird die Seite mit der unten angegebenen ID eingefügt. Mit dem Attribut "gethttp" können Sie bestimmen, ob die Seite per http geholt werden soll oder nicht. Das Attribut seeMode bestimmt, ob die Datei im seeMode als Include Datei bearbeitet werden kann, dies ist allerdings nur möglich wenn das Dokument per id included wird.',
        attributes: {
            type: {
                document: 3,
                template: 3
            },
            template: 2,
            id: 2,
            path: 2,
            gethttp: {
                "true": 3,
                "false": 3
            },
            name: 2,
            id: 2
        }
    };
    top.dcms_tags["cp:input"] = {
        desc: 'Das cp:input-Tag bewirkt, daß im Bearbeitungsmodus des Dokumentes, das diese Vorlage zugrunde liegen hat, ein einzeiliges Eingabefeld erzeugt wird, wenn der Typ = "text" ausgewählt wird. Für die anderen Typen siehe Handbuch oder Hilfe.',
        attributes: {
            type: {
                text: 3,
                checkbox: 3,
                date: 3,
                choice: 3,
                select: 3
            },
            name: 2,
            size: 2,
            maxlength: 2,
            format: 2,
            mode: {
                add: 3,
                replace: 3
            },
            value: 2,
            values: 2,
            html: {
                "true": 3,
                "false": 3
            },
            htmlspecialchars: {
                "true": 3,
                "false": 3
            },
            php: {
                "true": 3,
                "false": 3
            },
            num_format: {
                german: 3,
                english: 3,
                french: 3,
                swiss: 3
            },
            precision: 2,
            win2iso: {
                "true": 3,
                "false": 3
            },
            reload: {
                "true": 3,
                "false": 3
            },
            seperator: 2,
            user: 2,
            spellcheck: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:js"] = {
        desc: "Das cp:js-Tag erzeugt ein HTML-Tag, das auf ein webEdition-internes Javascript-Dokument mit der unten angegebenen ID verweist. Dadurch können Sie Javascripts in einer separaten Datei definieren.",
        attributes: {
            id: 2,
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:keywords"] = {
        desc: 'Das cp:keywords-Tag erzeugt ein Schlüsselwort Meta-Tag. Alles zwischen Start- und Endtag wird als default-keywords eingetragen, falls das Schlüsselwortfeld in der Ansicht "Eigenschaft" leer ist. Ansonsten werden die Schlüsselworte aus der Ansicht "Eigenschaft" eingetragen.',
        attributes: {
            htmlspecialchars: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            xml: {
                "true": 3,
                "false": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };

    top.dcms_tags["cp:newsletterConfirmLink"] = {
        desc: "Dieser Tag dient dazu, einen Bestätigungs-Link für einen Double-Opt-In zu erstellen. Ein Newsletter-Interessent kann so bestätigen, dass er den Newsletter abonnieren möchte.",
        attributes: {
            plain: {
                "true": 3,
                "false": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:newsletterField"] = {
        desc: "Ein Feld aus dem Empfängerdatensatz innerhalb eines Newsletters anzeigen.",
        attributes: {
            fieldName: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:newsletterSalutation"] = {
        desc: "Mit diesem Tag kann man Anrede-Felder anzeigen.",
        attributes: {
            type: {
                email: 3,
                salutation: 3,
                title: 3,
                firstname: 3,
                lastname: 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:newsletterUnsubscribeLink"] = {
        desc: "Das cp:newsletterUnsubscribeLink-Tag erzeugt ein HTML-Link-Tag zum Austragen aus der Newsletterliste. Dieses Tag kann nur in E-Mail Vorlagen benutzt werden!",
        attributes: {
            id: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
	
    top.dcms_tags["cp:next"] = {
        desc: "Das cp:next-Tag erzeugt ein HTML-Link-Tag, das auf die nächste cp:listview-Seite verweist. Der gesamte Inhalt zwischen Start- und Endtag wird verlinkt.",
        attributes: {
            "class": 2,
            style: 2,
            xml: {
                "true": 3,
                "false": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
	
    top.dcms_tags["cp:noCache"] = {
        desc: "Innerhalb dieses Tags kann PHP-Code stehen, welcher bei einer gecachten Vorlage (Ausnahme: Full-Cache) immer ausgeführt werden soll.",
        attributes: {
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
	
    top.dcms_tags["cp:quicktime"] = {
        desc: 'Das cp:quicktime-Tag dient dazu, einen Quicktime Movie in den Inhalt des Dokumentes einzubauen. Im Bearbeitungsmodus eines Dokumentes, das diese Vorlage zugrunde liegen hat, ist ein Button "edit" sichtbar. Durch Anklicken dieses Buttons, öffnet sich ein Dateimanager, in dem man einen Quicktime Movie, der zuvor in webEdition angelegt wurde, auswählen kann. Für das Tag cp:quicktime gibt es momentan leider keine xhtml-valide Ausgabe, die auf gängigen Browsern korrekt ausgeführt wird. Daher wird dem Attribut "xml" unabhängig von der hier gemachten Einstellung immer der Wert "false" zugeordnet.',
        attributes: {
            name: 2,
            width: 2,
            height: 2,
            startid: 2,
            parentid: 2,
            showcontrol: {
                "true": 3,
                "false": 3
            },
            showquicktime: {
                "true": 3,
                "false": 3
            },
            xml: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            sizingrel: 2,
            sizingstyle: {
                none: 3,
                em: 3,
                ex: 3,
                "%": 3,
                px: 3
            },
            sizingbase: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };

    top.dcms_tags["cp:repeat"] = {
        desc: "Dieses Tag dient dazu, den umschlossenen Inhalt innerhalb von <cp:listview> pro gefundenem Eintrag zu wiederholen.",
        attributes: {
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:search"] = {
        desc: 'Das cp:search-Tag erzeugt ein Eingabefeld oder ein Textfeld, das für Suchanfragen genutzt werden soll. Das Suchfeld hat intern den Namen "we_lv_search_0". Wenn die Suchform also gesendet wird, dann wird auf der empfangenden Webseite die PHP-Variable $_REQUEST["we_lv_search_0"] mit dem Inhalt des Eingabefeldes gefüllt sein.',
        attributes: {
            type: {
                textinput: 3,
                textarea: 3,
                print: 3
            },
            name: 2,
            value: 2,
            size: 2,
            maxlength: 2,
            cols: 2,
            rows: 2,
            xml: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:subscribe"] = {
        desc: 'Dieses Tag erzeugt ein Eingabefeld zum Eintragen in die Newsletter-Liste. Mit dem Attribut "type" kann bestimmt werden, um welche Art Feld es sich handelt.',
        attributes: {
            type: {
                email: 3,
                htmlCheckbox: 3,
                htmlSelect: 3,
                firstname: 3,
                lastname: 3,
                salutation: 3,
                title: 3,
                listCheckbox: 3,
                listSelect: 3
            },
            size: 2,
            maxlength: 2,
            value: 2,
            values: 2,
            "class": 2,
            style: 2,
            onchange: 2,
            checked: {
                "true": 3,
                "false": 3
            },
            xml: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:textarea"] = {
        desc: "Das cp:textarea-Tag erzeugt ein mehrzeiliges Eingabefeld.",
        attributes: {
            name: 2,
            cols: 2,
            rows: 2,
            autobr: {
                "true": 3,
                "false": 3
            },
            importrtf: {
                "true": 3,
                "false": 3
            },
            width: 2,
            height: 2,
            bgcolor: 2,
            html: {
                "true": 3,
                "false": 3
            },
            htmlspecialchars: {
                "true": 3,
                "false": 3
            },
            php: {
                "true": 3,
                "false": 3
            },
            wysiwyg: {
                "true": 3,
                "false": 3
            },
            commands: {
                acronym: 3,
                anchor: 3,
                applystyle: 3,
                backcolor: 3,
                bold: 3,
                caption: 3,
                color: 3,
                copy: 3,
                copypaste: 3,
                createlink: 3,
                cut: 3,
                decreasecolspan: 3,
                deletecol: 3,
                deleterow: 3,
                editcell: 3,
                editsource: 3,
                edittable: 3,
                fontname: 3,
                fontsize: 3,
                forecolor: 3,
                formatblock: 3,
                fullscreen: 3,
                importrtf: 3,
                increasecolspan: 3,
                indent: 3,
                insertbreak: 3,
                insertcolumnleft: 3,
                insertcolumnright: 3,
                inserthorizontalrule: 3,
                insertimage: 3,
                insertorderedlist: 3,
                insertrowabove: 3,
                insertrowbelow: 3,
                insertspecialchar: 3,
                inserttable: 3,
                insertunorderedlist: 3,
                italic: 3,
                justify: 3,
                justifycenter: 3,
                justifyfull: 3,
                justifyleft: 3,
                justifyright: 3,
                lang: 3,
                link: 3,
                list: 3,
                outdent: 3,
                paste: 3,
                prop: 3,
                redo: 3,
                removecaption: 3,
                removeformat: 3,
                removetags: 3,
                spellcheck: 3,
                strikethrough: 3,
                subscript: 3,
                superscript: 3,
                underline: 3,
                table: 3,
                undo: 3,
                unlink: 3,
                visibleborders: 3
            },
            fontnames: {
                arial: 3,
                courier: 3,
                tahoma: 3,
                times: 3,
                verdana: 3,
                wingdings: 3
            },
            xml: {
                "true": 3,
                "false": 3
            },
            abbr: {
                "true": 3,
                "false": 3
            },
            removefirstparagraph: {
                "true": 3,
                "false": 3
            },
            inlineedit: {
                "true": 3,
                "false": 3
            },
            buttonpos: {
                top: 3,
                bottom: 3
            },
            win2iso: {
                "true": 3,
                "false": 3
            },
            classes: 2,
            spellcheck: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:unsubscribe"] = {
        desc: "Dieses Tag erzeugt ein Eingabefeld zum Austragen aus der Newsletter-Liste. Dieser Tag muss innerhalb eines Formulars platziert werden.<br />Auf der Folgeseite muss der Tag <cp:addDelNewsletterEmail/> vorhanden sein, bei diesem legen Sie auch den Speicherort der CSV Empfängerlisten fest, aus denen der Empfänger ausgetragen werden soll.",
        attributes: {
            size: 2,
            maxlength: 2,
            value: 2,
            "class": 2,
            style: 2,
            onchange: 2,
            xml: {
                "true": 3,
                "false": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:url"] = {
        desc: "Das cp:url-Tag erzeugt eine webEdition-interne URL, die auf das Dokument mit der unten angegebenen ID verlinkt.",
        attributes: {
            id: 2,
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:var"] = {
        desc: "Das cp:var-Tag zeigt den Inhalt einer globalen Php-Variablen bzw. den Inhalt eines Dokumentfeldes mit dem unten eingegebenen Namen an.",
        attributes: {
            name: 2,
            type: {
                document: 3,
                property: 3,
                global: 3,
                img: 3,
                href: 3,
                date: 3,
                link: 3,
                multiobject: 3,
                request: 3,
                select: 3,
                session: 3,
                shopVat: 3
            },
            doc: {
                self: 3,
                top: 3
            },
            win2iso: {
                "true": 3,
                "false": 3
            },
            htmlspecialchars: {
                "true": 3,
                "false": 3
            },
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            },
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:writeVoting"] = {
        desc: 'Dieses Tag schreibt ein Voting in die Datenbank. Falls das Attribut "id" definiert ist, wird nur das Voting mit dieser id gespeichert.<br /><br />Hinweis: WICHTIG! Das Tag <cp:writeVoting/> muss in der allerersten Zeile der Vorlage stehen, in der es verwendet wird. Andernfalls ist eine Überprüfung des Abstimmungsintervalls per COOKIE nicht möglich!',
        attributes: {
            id: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };
    top.dcms_tags["cp:xmlfeed"] = {
        desc: "Das cp:xmlfeed Tag lädt den XML-Inhalt von der eingegebenen URL.",
        attributes: {
            name: 2,
            url: 2,
            refresh: 2,
            cachelifetime: 2,
            TagReferenz: {
                "": 3,
                "": 3
            }
        }
    };

