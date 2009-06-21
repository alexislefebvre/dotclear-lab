<?xml version="1.0" encoding="utf-8"?>
<!--
	
	xhtml2odt - XHTML to ODT XML transformation.
    Copyright (C) 2009 Aurelien Bompard
    Based on the work on docbook2odt, by Roman Fordinal
	http://open.comsultia.com/docbook2odf/
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
-->
<xsl:stylesheet
	version="1.0"
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" 
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
	xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
	xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:xforms="http://www.w3.org/2002/xforms"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0"
	office:class="text"
	office:version="1.0">


<xsl:template name="table.number">
	<!-- compute number of section -->
	<xsl:value-of select="count(preceding::informaltable)+count(preceding::table)+1"/>
</xsl:template>

<xsl:template match="table">
    <xsl:if test="caption">
        <xsl:variable name="number">
            <xsl:call-template name="table.number"/>
        </xsl:variable>
        <text:h text:style-name="Heading-small">
            <xsl:text>Table </xsl:text><xsl:value-of select="$number"/><xsl:text>. </xsl:text><xsl:value-of select="caption"/>
        </text:h>
    </xsl:if>
    <table:table table:style-name="table-default">
        <table:table-column>
            <xsl:attribute name="table:number-columns-repeated">
                <xsl:value-of select="count(descendant::tr[1]/th|descendant::tr[1]/td)"/>
            </xsl:attribute>
        </table:table-column>
        <!--<xsl:attribute name="table:name"></xsl:attribute>-->
        <xsl:apply-templates/>
    </table:table>
</xsl:template>

<xsl:template match="table/caption"/>

<xsl:template match="thead">
	<table:table-header-rows>
		<xsl:apply-templates/>
	</table:table-header-rows>
</xsl:template>

<xsl:template match="tfoot">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="tbody">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="tr">
	<xsl:choose>
		<!-- this is header -->
		<xsl:when test="th">
			<table:table-header-rows>
				<table:table-row>
					<xsl:apply-templates/>
				</table:table-row>
			</table:table-header-rows>
		</xsl:when>
		<xsl:otherwise>
			<table:table-row>
				<xsl:apply-templates/>
			</table:table-row>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>


<!-- td -->

<xsl:template match="td|th">
	
	<xsl:variable name="position" select="position()"/>
	<xsl:variable name="count" select="last()"/>
    <xsl:variable name="vertical-position" select="count(../preceding-sibling::tr) + 1"/>
	<xsl:variable name="vertical-count" select="count(../../*)"/>
	
	<xsl:comment>position=<xsl:value-of select="$position"/></xsl:comment>
	<xsl:comment>count=<xsl:value-of select="$count"/></xsl:comment>
	<xsl:comment>vertical-position=<xsl:value-of select="$vertical-position"/></xsl:comment>
	<xsl:comment>vertical-count=<xsl:value-of select="$vertical-count"/></xsl:comment>
	
	<table:table-cell office:value-type="string">
		
		<xsl:attribute name="table:style-name">
			<xsl:text>table-default.cell-</xsl:text>
			<!-- prefix -->
			<xsl:if test="local-name() = th">
				<xsl:text>H-</xsl:text>
			</xsl:if>
			<xsl:if test="parent::tr/parent::tfoot">
				<xsl:text>F-</xsl:text>
			</xsl:if>
			<!-- postfix defined by cell position -->
			<!--
				__________
				|A1|B1|C1|
				|A2|B2|C2|
				|A3|B3|C3|
				^^^^^^^^^^
			-->
			<xsl:choose>
			
				<!-- A3 -->
				<xsl:when test="$position = 1 and $vertical-position = $vertical-count">
					<xsl:text>A3</xsl:text>
				</xsl:when>
				<!-- C3 -->
				<xsl:when test="$position=$count and $vertical-position = $vertical-count">
					<xsl:text>C3</xsl:text>
				</xsl:when>
				<!-- B3 -->
				<xsl:when test="$vertical-position = $vertical-count">
					<xsl:text>B3</xsl:text>
				</xsl:when>
			
				<!-- A1 -->
				<xsl:when test="$position = 1 and $vertical-position = 1">
					<xsl:text>A1</xsl:text>
				</xsl:when>
				<!-- C1 -->
				<xsl:when test="$position=$count and $vertical-position = 1">
					<xsl:text>C1</xsl:text>
				</xsl:when>
				<!-- B1 -->
				<xsl:when test="$vertical-position = 1">
					<xsl:text>B1</xsl:text>
				</xsl:when>
				
				<!-- A2 -->
				<xsl:when test="$position = 1">
					<xsl:text>A2</xsl:text>
				</xsl:when>
				<!-- C2 -->
				<xsl:when test="$position=$count">
					<xsl:text>C2</xsl:text>
				</xsl:when>
				
				<!-- all other cells -->
				<xsl:otherwise>
					<xsl:text>B2</xsl:text>
				</xsl:otherwise>
				
			</xsl:choose>
			
		</xsl:attribute>
		
        <text:p>
            <xsl:choose>
                <xsl:when test="local-name() = 'th'">
                    <xsl:attribute name="text:style-name">Table_20_Heading</xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="text:style-name">Table_20_Contents</xsl:attribute>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:apply-templates/>
        </text:p>
	</table:table-cell>
</xsl:template>


</xsl:stylesheet>
