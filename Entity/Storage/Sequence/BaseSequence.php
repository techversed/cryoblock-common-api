<?php

namespace Carbon\ApiBundle\Entity\Storage\Sequence;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;

/*

    It did not make sense for us to store sequence id and sample id on the base class since it meant that the class could only be implemented by a single type of sequence.

    The whole purpose of having a base class it to make it so that a class can be instantiated by a series of subclasses and have a set group of code work on all of them...
    Instead I have decided to store a series of tools which could be helpful to everything right here in the class itself --

    The helper functions included here should help with the following tasks:
        Convert nucleotide sequences to amino acid sequences.
        Convert an amino acid sequence to a generic nuelcotide sequence -- made up of the first codon in the table for each amino acid
        Take the reverse compliment of a given nucleotide sequence.
        Check if two nucleotide sequences are equal to each other
        Check if to amino acid sequences are the same as each otehr
        check if an nucleotide sequence matches a given amino acid sequence
        Generate the reverse complement of a nucleotide sequence
*/


/** @ORM\MappedSuperclass */
abstract class BaseSequence extends BaseCryoblockEntity
{
    // Don't know what else we are going to need to implment here

    // I decided to move a lot of this into the crowelab repo instead of the cryoblock common repo -- it just was not working out.
    // Base sequence should be set up to handle any type of sequence and we are really only going to support
    protected $aminoToNucLookup = array(
        'I' => array('ATT', 'ATC', 'ATA'),
        'L' => array('CTT', 'CTC', 'CTA', 'CTG', 'TTA', 'TTG'),
        'V' => array('GTT', 'GTC', 'GTA', 'GTG'),
        'F' => array('TTT', 'TTC'),
        'M' => array('ATG'),
        'C' => array('TGT', 'TGC'),
        'A' => array('GCT', 'GCC', 'GCA', 'GCG'),
        'G' => array('GGT', 'GGC', 'GGA', 'GGG'),
        'P' => array('CCT', 'CCC', 'CCA', 'CCG'),
        'T' => array('ACT', 'ACC', 'ACA', 'ACG'),
        'S' => array('TCT', 'TCC', 'TCA', 'TCG', 'AGT', 'AGC'),
        'Y' => array('TAT', 'TAC'),
        'W' => array('TGG'),
        'Q' => array('CAA', 'CAG'),
        'N' => array('AAT', 'AAC'),
        'H' => array('CAT', 'CAC'),
        'E' => array('GAA', 'GAG'),
        'D' => array('GAT', 'GAC'),
        'K' => array('AAA', 'AAG'),
        'R' => array('CGT', 'CGC', 'CGA', 'CGG', 'AGA', 'AGG'),
        'X' => array('TAA', 'TAG', 'TGA')
    );

    // Array that is used to and in converstion from nucleotides to amino acids
    protected $nucToAminoLookup = array(
        'A' => array(
            'A' => array(
                'A' => 'K',
                'T' => 'N',
                'C' => 'N',
                'G' => 'K'
            ),
            'T' => array(
                'A' => 'I',
                'T' => 'I',
                'C' => 'I',
                'G' => 'M'
            ),
            'C' => array(
                'A' => 'T',
                'T' => 'T',
                'C' => 'T',
                'G' => 'T'
            ),
            'G' => array(
                'A' => 'R',
                'T' => 'S',
                'C' => 'S',
                'G' => 'R'
            )
        ),
        'T' => array(
            'A' => array(
                'A' => 'X',
                'T' => 'Y',
                'C' => 'Y',
                'G' => 'X'
            ),
            'T' => array(
                'A' => 'L',
                'T' => 'F',
                'C' => 'F',
                'G' => 'L'
            ),
            'C' => array(
                'A' => 'S',
                'T' => 'S',
                'C' => 'S',
                'G' => 'S'
            ),
            'G' => array(
                'A' => 'X',
                'T' => 'C',
                'C' => 'C',
                'G' => 'W'
            )
        ),
        'C' => array(
            'A' => array(
                'A' => 'Q',
                'T' => 'H',
                'C' => 'H',
                'G' => 'Q'
            ),
            'T' => array(
                'A' => 'L',
                'T' => 'L',
                'C' => 'P',
                'G' => 'L'
            ),
            'C' => array(
                'A' => 'P',
                'T' => 'P',
                'C' => 'P',
                'G' => 'P'
            ),
            'G' => array(
                'A' => 'R',
                'T' => 'R',
                'C' => 'R',
                'G' => 'R'
            )
        ),
        'G' => array(
            'A' => array(
                'A' => 'E',
                'T' => 'D',
                'C' => 'D',
                'G' => 'E'
            ),
            'T' => array(
                'A' => 'V',
                'T' => 'V',
                'C' => 'V',
                'G' => 'V'
            ),
            'C' => array(
                'A' => 'A',
                'T' => 'A',
                'C' => 'A',
                'G' => 'A'
            ),
            'G' => array(
                'A' => 'G',
                'T' => 'G',
                'C' => 'G',
                'G' => 'G'
            )
        )
    );


    //nucToAmino
    //Converts a string of nucleic acids to a stirng of amino acids
    function nucToAmino($nucSequence)
    {
        $length = 3;
        $nucSequence = strtoupper($nucSequence);
        $aminoSequence = '';

        for($start = 0; $start <strlen($nucSequence) / $length; $start++){
            $triplet = substr($nucSequence, $start * $length, $length);
            $aminoSequence .= $this->nucToAminoLookup[$triplet[0]][$triplet[1]][$triplet[2]];
        }

        return $aminoSequence;
    }

    // Takes an amino acid sequence returns a string of nucleotides that code for the amino sequence (just uses the first listed codon in the above datastructure -- nothing fancy)
    function aminoToNuc($aminoSequence)
    {
        $aminoSequence = strtoupper($aminoSequence);

        $nucSequence = '';

        for($start = 0; $start < strlen($aminoSequence); $start++){

            $nucSequence .= $this->aminoToNucLookup[substr($aminoSequence,$start,1)][0];

        }

        return $nucSequence;
    }

    // Check to see if two nucleotide sequences code for the same amino sequence
        // Returns boolean
    function nucAminoMatch($seq1, $seq2)
    {
        return ($this->nucToAmino($seq1) == $this->nucToAmino($seq2));
    }

    // Function to test if nucleotide sequences are exact matches with another
        // Returns boolean
    function nucNucMatch($seq1, $seq2)
    {
        return strtoupper($seq1) == strtoupper($seq2);
    }

    // Function to test if two amino acid sequences match one another
        // Returns boolean
    function aminoAminoMatch($seq1, $seq2)
    {
        return strtoupper($seq1) == strtoupper($seq2);
    }

    function reverseComplement($seq, $rnaInput = false, $rnaOutput = false){
        $seq = $rnaInput ? str_replace("U", "T", strtoupper($seq) ) : strtoupper($seq);

        $complementMap = array(
            "A" => 'T',
            "T" => 'A',
            "G" => 'C',
            "C" => 'G'
        );

        $test = array();
        $i = strlen($seq) -1;

        while($i>=0){
            $test[] = $complementMap[$seq[$i]];
            $i = $i-1;
        }

        $output = implode($test);

        return $rnaOutput ? str_replace("U", "T", $output ) : $output;
    }

}
