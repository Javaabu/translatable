---
title: Difference between DB and JSON translatable
description: The "But why..." of DB and JSON translatables
sidebar_position: 70
---

Functionally, both `IsDbTranslatable` and `IsJsonTranslatable` both implement all functions of `Translatable`, there are no differences in syntax when using either of these.

But there are a few notable differences in how each type implements `Translatables`. Depending on the use case and scale of the application, one may make more sense than the other.

| IsDbTranslatable                                        | IsJsonTranslatable                                          |
|---------------------------------------------------------|-------------------------------------------------------------|
| - Works by creating additional rows for each language   | - Works by adding a `translatables` JSON field to every row |
| - Additional queries required to check if locale exists | - No additional queries needed for translating              |
| - Indexable by default                                  | - Needs to search through JSON field                        |
