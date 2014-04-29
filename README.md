OpenStudio Node.js
==================
This app uses Node.js Bindings of OpenStudio to enable web-based building energy simulations coded entirely in one language: JavaScript.

http://node.eebhub.org

Background
---------
This project began by porting a minimum version of the DOE Energy Efficient Building Hub's [Retrofit Manager Tool](http://tools.eebhub.org/comprehensive).  This predecessor app comprised JavaScript, PHP, and Ruby.  Each additional programming language expanded the potential for error and increased the difficulty to train new team members.  The main objective of OpenStudio Node.js was to simplify the software into 1 language.  

* **OpenStudio** = a software development kit to support whole building energy modeling using EnergyPlus and advanced daylight analysis using Radiance (developed by NREL)
* **Node.js** = a server-side implementation of JavaScript running on Google's V8 Engine

As of April 2014, the working web application above uses one language, JavaScript, on both the front-end (browser) and back-end (server).

Learn More at http://www.buildsci.us/openstudio-nodejs.html.

Steps to Learn
--------------
1. [Install OpenStudio & EnergyPlus](https://github.com/buildsci/energyplus.io/tree/develop/installers) on Ubuntu Linux 12.04 (only currently supported operating system)
2. [Install Node.js](http://joshwentz.blogspot.com/2013/05/install-nodejs-on-ubuntu-1204.html)
3. [**Run OpenStudio with Node.js**](https://github.com/eebhub/openstudio_nodejs/tree/develop/library): minimum version requires only 3 files
4. [**Run OpenStudio Ruby Measures with Node.js**](https://github.com/eebhub/openstudio_nodejs/tree/develop/jason):  shows how to add 3 measures from NREL's Building Component Library

Software Stack
--------------

This building energy software would not have been possible without the 
Open Source Software Community to which it is built upon: 

* Bootstrap Front End Styling
* Express Web Framework
* Socket.io HTML5 WebSockets
* Node.js Server Side Language
* Ubuntu Operating System

To contribute to this community, this code is released under an open source license inherited from the [EEB Hub Simulation Platform](https://github.com/eebhub/platform/blob/develop/LICENSE), based off of the MIT License.

Team
-----
Built by the [Energy Efficient Buildings Hub (EEB Hub)](http://www.eebhub.org), a U.S. Department of Energy Innovation Hub, in collaboration with the [Building Science GROUP](http://www.buildsci.us) and the [National Renewable Energy Laboratory (NREL)](https://openstudio.nrel.gov/).  Funding was provided by both the DOE and Dr. Jelena Srebric.
